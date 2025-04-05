<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Equipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth', methods: ['GET', 'POST'])]
    public function showAuthPage(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils
    ): Response {
        if ($request->isMethod('POST')) {
            $formType = $request->request->get('_form_type');
            
            if ($formType === 'signup') {
                return $this->handleSignUp($request, $em, $passwordHasher);
            }
        }

        return $this->render('auth/auth.html.twig', [
            'signin_error' => $authenticationUtils->getLastAuthenticationError()?->getMessageKey(),
        ]);
    }

    private function handleSignUp(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        
        try {
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            $user->setEmail($request->request->get('email'));
            
           
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )
            );
            
            $user->setRole('USER');
            $user->setSoldeConge(30);  
            


            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Registration successful! Please login.');
            return $this->redirectToRoute('app_auth');
            
        } catch (\Exception $e) {
            $this->addFlash('signup_error', 'Registration failed: '.$e->getMessage());
            return $this->redirectToRoute('app_auth');
        }
    }
}