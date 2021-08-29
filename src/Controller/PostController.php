<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Video;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises
            $images = $form->get('photos')->getData();
            $videos = $form->get('videos')->getData();

            // On boucle sur les images
            foreach($images as $image){
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
            foreach($videos as $video){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $video->guessExtension();

                // On copie le fichier dans le dossier upload
                $video->move(
                    $this->getParameter('videos_directory'),
                    $fichier);

                // On stocke la vidéo dans la base de données (son nom)
                $videos = new Video;
                $videos->setName($fichier);
                $post->addVideo($videos);
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

            $entityManager = $this->getDoctrine()->getManager();

            // On stocke l'image principale dans la base de données (son nom)
            $entityManager->persist($mainImage);
            // On stocke la date dans la base de données
            $entityManager->persist($date);
            $entityManager->persist($post);
            
            $entityManager->flush();

            return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index', [], Response::HTTP_SEE_OTHER);
    }
}
