<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
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
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
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
            $slugger = new AsciiSlugger('fr', ['fr' => [' ' => '-', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u']]);
            $post->setSlug($slugger->slug($post->getName()));

            $entityManager = $this->getDoctrine()->getManager();
          
            // On stocke l'image principale dans la base de données (son nom)
            //$entityManager->persist($mainImage);
            // On stocke la date dans la base de données
            //$entityManager->persist($date);
            $entityManager->persist($post);
            
            $entityManager->flush();

            return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="post_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger('fr', ['fr' => [' ' => '-', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u']]);
            $post->setSlug($slugger->slug($post->getName()));

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}", name="post_show", methods={"GET"})
     * @Route("/{slug}/{page}", name="post_show_with_parameter_commentary", methods={"GET"})
     */
    public function show($slug, $page = null, Post $post, PostRepository $postRepository, Request $request, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $limitComments = 5;
        $totalComments = count($commentRepository->findBy(['post' => $post], ['date' => 'desc']));
        
        return $this->render('post/show.html.twig', [
            'post' => $postRepository->findOneBy(['slug' => $slug]),
            'comments' => $commentRepository->findBy(['post' => $post], ['date' => 'desc'], $limitComments, $page * $limitComments),
            'form' => $form->createView(),
            'pages' => $totalComments / $limitComments,
        ]);
    }

    /**
     * @Route("/{slug}", name="post_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getSlug(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
    }
}
