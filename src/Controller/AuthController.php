<?php

// src/Controller/AuthController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth', methods: ['GET', 'POST'])]
    public function auth(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        SessionInterface $session
    ) {
        if ($request->isMethod('POST')) {
            $formType = $request->request->get('_form_type');

            if ($formType === 'signup') {
                return $this->handleSignUp($request, $session, $mailer,$em);
            } elseif ($formType === 'verify_code') {
                return $this->handleVerification($request, $session, $em, $passwordHasher);
            }
            elseif($formType === 'signin'){
                return $this->handleLogin($request, $em, $passwordHasher, $session);
            }
        }

        return $this->render('auth/auth.html.twig', [
            'show_verification_modal' => false,
        ]);
    }

    private function handleLogin(Request $request , EntityManagerInterface $em , UserPasswordHasherInterface $passwordHasher, sessionInterface $session){
        $email = $request->request->get('email') ; 
        $password = $request->request->get('password') ;
        
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]) ; 

        if(!$user || !$passwordHasher->isPasswordValid($user, $password)){
            $this->addFlash('signin_error', 'Invalid email or password.');
            return $this->redirectToRoute('app_auth');
        }
        $session->set('user_id', $user->getId());
        $session->set('user_email', $user->getEmail());
        $session->set('user_role', $user->getRole());
        $session->set('user_last_name', $user->getLastName());
    
       
        return $this->redirectToRoute('app_home');
    }


    private function handleSignUp(Request $request, SessionInterface $session, MailerInterface $mailer ,EntityManagerInterface $em)
    {
        $exist = $em->getRepository(User::class)->findOneBy(['email' =>  $request->request->get('email')]) ; 
        if($exist){
            $this->addFlash('signup_error', 'Email is already in use.');
            return $this->redirectToRoute('app_auth');

        }
        $userData = [
            'first_name' => $request->request->get('first_name'),
            'last_name' => $request->request->get('last_name'),
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
        ];

        $verificationCode = random_int(100000, 999999);
        $session->set('pending_user', $userData);
        $session->set('verification_code', $verificationCode);

        $email = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->to($userData['email'])
            ->subject('Your Verification Code')
            ->text("Your verification code is: $verificationCode");

        $mailer->send($email);

        return $this->render('auth/auth.html.twig', [
            'show_verification_modal' => true,
        ]);
    }

    private function handleVerification(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $submittedCode = $request->request->get('verification_code');
        $storedCode = $session->get('verification_code');
        $userData = $session->get('pending_user');

        if ($submittedCode == $storedCode && $userData) {
            $user = new User();
            $user->setFirstName($userData['first_name']);
            $user->setLastName($userData['last_name']);
            $user->setEmail($userData['email']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $userData['password'])
            );
            $user->setRole('USER');
            $user->setSoldeConge(30);

            $em->persist($user);
            $em->flush();

            $session->remove('pending_user');
            $session->remove('verification_code');

            $this->addFlash('success', 'Registration successful! Please login.');
            return $this->redirectToRoute('app_auth');
        }

        $this->addFlash('signup_error', 'Invalid verification code.');
        return $this->redirectToRoute('app_auth');
    }

    #[Route('/logout', name: 'app_logout')]
    public function signout(SessionInterface $session)
    {
        $session->clear();
        return $this->redirectToRoute('app_auth');
    }
    
    
}
