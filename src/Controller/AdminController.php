<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("admin/create")
     */
    public function create(EntityManagerInterface $manager, Request $request){
        $post = new Post;

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            echo"testtt";
            $manager->persist($post);
            $manager->flush();
            
        }
        
        return $this->render('admin/index.html.twig', [
                'form' => $form->createView()
            ]);
    }
}