<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityEvents;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hash, EventDispatcherInterface $dispatcher, MailerInterface $mailer){
        $user = new User;

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() AND $form->isValid()){
            $hash = $hash->hashPassword($user, $user->getPassword());

            $user->setPassword($hash);
            
            $manager->persist($user);
            $manager->flush();

            $email = (new Email())
            ->from('hello@snow-trick.com')
            ->to('abdessamad.bannouf@laposte.net')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Votre inscription sur Snow Trick!')
            ->text('Votre inscription sur Snow Tricks
            
Vous venez de créer votre compte sur le meilleur site communautaire de Snow Board avec le mail '.$user->getEmail().', et nous vous en en remercions cher(e) '.$user->getUsername().' !
                    
Snow Tricks - Blog')
            ->html('Votre inscription sur Snow Tricks<br/><br>Vous venez de créer votre compte sur le meilleur site communautaire de Snow Board avec le mail '.$user->getEmail().', et nous vous en en remercions cher(e) '.$user->getUsername().' !<br/><br/>Snow Tricks - Blog');

        $mailer->send($email);

            // Redirect after registration
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get("security.token_storage")->setToken($token);

            $event = new SecurityEvents($request);
            $dispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

            return $this->redirectToRoute('home');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}