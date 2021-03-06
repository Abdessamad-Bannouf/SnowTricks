<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    private $commentRepository;
    private $postRepository;

    public function __construct(CommentRepository $commentRepository, PostRepository $postRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/", name="comment_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $this->commentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="comment_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        $content = $form->get('content')->getData();
        $date = new \DateTime();

        if ($form->isSubmitted() && $form->isValid()) {
            $postId = $form->get('postId')->getData();
            
            $post = $this->postRepository->findBy(['id'=>$postId]);
            $comment->setContent($content);
            $comment->setPost($post[0]);
            $comment->setUser($this->getUser());
            $comment->setDate($date);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $getPostWithComment  = $this->postRepository->findBy(['id' => $comment->getPost()]);
            $slug = $getPostWithComment[0]->getSlug();

            return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="comment_show", methods={"GET"})
     */
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="comment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $getPostWithComment  = $this->postRepository->findBy(['id' => $comment->getPost()]);
            $slug = $getPostWithComment[0]->getSlug();

            return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="comment_delete", methods={"POST"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        $getPostWithComment  = $this->postRepository->findBy(['id' => $comment->getPost()]);
        $slug = $getPostWithComment[0]->getSlug();

        return $this->redirectToRoute('post_show', ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }
}
