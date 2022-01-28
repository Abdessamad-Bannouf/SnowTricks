<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use App\Form\UpdatePhotoType;
use App\Repository\PhotoRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/photo")
 */
class PhotoController extends AbstractController
{
    /**
     * @Route("/", name="photo_index", methods={"GET"})
     */
    public function index(PhotoRepository $photoRepository): Response
    {
        return $this->render('photo/index.html.twig', [
            'photos' => $photoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="photo_new", methods={"GET","POST"})
     */
    public function new(Request $request, PostRepository $postRepository): Response
    {
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($photo);
            $entityManager->flush();

            $getPostWithPhoto  = $postRepository->findBy(['id' => $photo->getPost()]);
            $slug = $getPostWithPhoto[0]->getSlug();

            return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('photo/new.html.twig', [
            'photo' => $photo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="photo_show", methods={"GET"})
     */
    public function show(Photo $photo): Response
    {
        return $this->render('photo/show.html.twig', [
            'photo' => $photo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="photo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Photo $photo, PostRepository $postRepository): Response
    {
        $form = $this->createForm(UpdatePhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère l'image principale qui va servir pour la page pour afficher la liste
            $postPhoto = $form->get('file')->getData();
            $image = new File($postPhoto);
            $fichier = md5(uniqid()) . '.' . $image->guessExtension();

            // On copie le fichier dans le dossier upload
            $postPhoto->move(
                $this->getParameter('images_directory'),
                $fichier);
            $postPhoto = $photo->setName($fichier);
            $photo->setName($photo->getName());
            $this->getDoctrine()->getManager()->flush();

            $getPostWithPhoto  = $postRepository->findBy(['id' => $photo->getPost()]);
            $slug = $getPostWithPhoto[0]->getSlug();

            return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('photo/edit.html.twig', [
            'photo' => $photo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="photo_delete", methods={"POST"})
     */
    public function delete(Request $request, Photo $photo, PostRepository $postRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$photo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($photo);
            $entityManager->flush();
        }

        $getPostWithPhoto  = $postRepository->findBy(['id' => $photo->getPost()]);
        $slug = $getPostWithPhoto[0]->getSlug();

        return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }
}
