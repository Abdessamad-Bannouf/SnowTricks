<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PhotoRepository;
use App\Repository\PostRepository;
use App\Repository\VideoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    private $asciSlugger;
    private $commentRepository;
    private $photoRepository;
    private $postRepository;
    private $videoRepository;

    public function __construct(CommentRepository $commentRepository, PhotoRepository $photoRepository, PostRepository $postRepository, VideoRepository $videoRepository)
    {
        $this->slugger = new AsciiSlugger('fr', ['fr' => [' ' => '-', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u']]);
        $this->commentRepository = $commentRepository;
        $this->photoRepository = $photoRepository;
        $this->postRepository = $postRepository;
        $this->videoRepository = $videoRepository;
    }

    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(): Response
    {
        if($this->getUser()) { 
            return $this->render('post/index.html.twig', [
                'posts' => $this->postRepository->findAll(),
            ]);
        }

        return $this->render('404/404.html.twig', []);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        // Si l'utilisateur est connecté il peut modifier un trick
        if($this->getUser())
        {
            $post = new Post();

            $entityManager = $this->getDoctrine()->getManager();
            
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // On récupère les images transmises
                $images = $form->get('photos')->getData();

                // On récupère les vidéos transmises
                $videos = $form->get('videos')->getData();

                // On boucle sur les images
                foreach($images as $image){
                    $image = new File($image->getName());
                    
                    // On génère un nouveau nom de fichier
                    $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                    // On copie le fichier dans le dossier upload
                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier);

                    // On stocke l'image dans la base de données (son nom)
                    $photo = new Photo;
                    $photo->setName($fichier);
                    $post->addPhoto($photo);
                }



                // On boucle sur les images
                foreach($videos as $link){
                    // On stocke la vidéo dans la base de données (son nom)
                    //$video = new Video;
                    $link->setName($link->getName());
                    $post->addVideo($link);
                }


                // On récupère l'image principale qui va servir pour la page pour afficher la liste
                $mainImage = $form->get('photo')->getData();
                $fichier = md5(uniqid()) . '.' . $mainImage->guessExtension();

                // On copie le fichier dans le dossier upload
                $mainImage->move(
                    $this->getParameter('images_directory'),
                    $fichier);
                $mainImage = $post->setPhoto($fichier);

                // On instancie la date
                $date = new \DateTime();
                $date = $post->setDate($date);

                //On persiste le nom sluggé dans la colonne slug
                $post->setSlug($this->slugger->slug($post->getName()));

                // On persiste le nom de l'utilisateur qui a ajouté le post (user connecté)
                $post->setUser($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
            
                // On stocke l'image principale dans la base de données (son nom)
                //$entityManager->persist($mainImage);
                // On stocke la date dans la base de données
                //$entityManager->persist($date);
                $entityManager->persist($post);
                
                $entityManager->flush();

                
                $this->addFlash('success-add-trick', 'Trick ajouté !');

                return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('post/new.html.twig', [
                'post' => $post,
                'form' => $form->createView()
            ]);     
        }
        
        return $this->render('404/404.html.twig', []);
    }

    /**
     * @Route("/{slug}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Post $post, Request $request): Response
    {
        // Si l'utilisateur est connecté il peut modifier un trick
        if($this->getUser()) {
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $post->setSlug($this->slugger->slug($post->getName()));

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success-edit-trick', 'Trick modifié !');

                return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('post/edit.html.twig', [
                'post' => $post,
                'form' => $form->createView()
            ]);
        }

        return $this->render('404/404.html.twig', []);
    }

    /**
     * @Route("/{slug}", name="post_show", methods={"GET"})
     * @Route("/{slug}/{page}", name="post_show_with_parameter_commentary", methods={"GET"})
     */
    public function show(PaginatorInterface $paginator, Post $post, $page = null, Request $request, $slug): Response
    {
        $comment = new Comment();
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $limitComments = 5;
        $totalComments = count($this->commentRepository->findBy(['post' => $post], ['date' => 'desc']));

        $photos = $this->photoRepository->findBy(['post' => $post]);
        $videos = $this->videoRepository->findBy(['post' => $post]);

        (int) $pages = intval($totalComments / $limitComments);

        // Create Pagination
        $data = $this->commentRepository->findBy(['post' => $post], ['date' => 'desc']);
    
        $comments = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('post/show.html.twig', [
            'post' => $this->postRepository->findOneBy(['slug' => $slug]),
            'photos' => $photos,
            'videos' => $videos,
            'comments' => $this->commentRepository->findBy(['post' => $post], ['date' => 'desc'], $limitComments, $page * $limitComments),
            'form' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/{slug}", name="post_delete", methods={"POST"})
     */
    public function delete(Post $post, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getSlug(), $request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $entityManager->remove($post);
            $entityManager->flush();
        }

        $this->addFlash('success-delete-trick', 'Trick supprimé !');
        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }
}
