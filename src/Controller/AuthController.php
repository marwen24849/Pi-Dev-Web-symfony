<?php

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
                return $this->handleSignUp($request, $session, $mailer, $em);
            } elseif ($formType === 'verify_code') {
                return $this->handleVerification($request, $session, $em, $passwordHasher);
            } elseif ($formType === 'signin') {
                return $this->handleLogin($request, $em, $passwordHasher, $session);
            } elseif ($formType === 'forgot_password') {
                return $this->handleForgotPassword($request, $em, $mailer, $session);
            } elseif ($formType === 'forgot_verify') {
                return $this->handleForgotVerify($request, $session);
            } elseif ($formType === 'reset_password') {
                return $this->handleResetPassword($request, $session, $em, $passwordHasher);
            }
        }

       
        return $this->render('auth/auth.html.twig', [
            'show_verification_modal'         => false,
            'show_forgot_verification_modal'  => false,
            'show_reset_password_modal'       => false,
        ]);
    }

    private function handleLogin(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SessionInterface $session
    ) {
        $email = $request->request->get('email');
        $passwordInput = $request->request->get('password');

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $passwordInput)) {
            $this->addFlash('signin_error', 'Invalid email or password.');
            return $this->redirectToRoute('app_auth');
        }

        $session->set('user_id', $user->getId());
        $session->set('user_email', $user->getEmail());
        $session->set('user_role', $user->getRole());
        $session->set('user_last_name', $user->getLastName());

        return $this->redirectToRoute('app_home');
    }

    private function handleSignUp(
        Request $request,
        SessionInterface $session,
        MailerInterface $mailer,
        EntityManagerInterface $em
    ) {
        $exist = $em->getRepository(User::class)->findOneBy(['email' => $request->request->get('email')]);
        if ($exist) {
            $this->addFlash('signup_error', 'Email is already in use.');
            return $this->redirectToRoute('app_auth');
        }

        $userData = [
            'first_name' => $request->request->get('first_name'),
            'last_name'  => $request->request->get('last_name'),
            'email'      => $request->request->get('email'),
            'password'   => $request->request->get('password'),
        ];

        $verificationCode = random_int(100000, 999999);
        $session->set('pending_user', $userData);
        $session->set('verification_code', $verificationCode);

        $emailMessage = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->to($userData['email'])
            ->subject('Your Verification Code')
            ->html("
    <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; text-align: center;'>
        <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
            <h2 style='color: #333333;'>🔐 Email Verification</h2>
            <p style='font-size: 16px; color: #666666;'>Hey there! Thanks for signing up. Use the code below to verify your email:</p>
            <div style='margin: 30px 0;'>
                <span style='display: inline-block; font-size: 28px; font-weight: bold; color: #ffffff; background-color: #007bff; padding: 15px 30px; border-radius: 8px; letter-spacing: 2px;'>
                    $verificationCode
                </span>
            </div>
            <hr style='margin: 30px 0;'>
            <p style='font-size: 12px; color: #cccccc;'>If you didn’t request this, just ignore this email.</p>
        </div>
    </div>
");


        $mailer->send($emailMessage);

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

    private function handleForgotPassword(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        SessionInterface $session
    ) {
        $emailInput = $request->request->get('forgot_email');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $emailInput]);

        if (!$user) {
            $this->addFlash('forgot_error', 'Email does not exist.');
            return $this->redirectToRoute('app_auth');
        }

        $forgotCode = random_int(100000, 999999);
        $session->set('forgot_code', $forgotCode);
        $session->set('forgot_email', $emailInput);

        $emailMessage = (new Email())
            ->from($_ENV['MAILER_FROM'])
            ->to($user->getEmail())
            ->subject('Password Reset Verification Code')
            ->html("
            <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; text-align: center;'>
                <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
                    <h2 style='color: #333333;'>🔑 Password Reset Request</h2>
                    <p style='font-size: 16px; color: #666666;'>Hey there! We received a request to reset your password. Use the code below to complete the process:</p>
                    <div style='margin: 30px 0;'>
                        <span style='display: inline-block; font-size: 28px; font-weight: bold; color: #ffffff; background-color: #007bff; padding: 15px 30px; border-radius: 8px; letter-spacing: 2px;'>
                            $forgotCode
                        </span>
                    </div>
                    <hr style='margin: 30px 0;'>
                    <p style='font-size: 12px; color: #cccccc;'>If you didn’t request this, just ignore this email. Your password won’t be changed.</p>
                </div>
            </div>
        ");
        

        $mailer->send($emailMessage);

        return $this->render('auth/auth.html.twig', [
            'show_forgot_verification_modal' => true,
        ]);
    }

    private function handleForgotVerify(
        Request $request,
        SessionInterface $session,
    ) {
        $submittedCode = $request->request->get('forgot_verification_code');
        $storedCode = $session->get('forgot_code');

        if ($submittedCode != $storedCode) {
            $this->addFlash('forgot_error', 'Invalid verification code.');
            return $this->redirectToRoute('app_auth');
        }

        return $this->render('auth/auth.html.twig', [
            'show_reset_password_modal' => true,
        ]);
    }

    private function handleResetPassword(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $newPassword = $request->request->get('new_password');
        $email = $session->get('forgot_email');
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('forgot_error', 'User not found.');
            return $this->redirectToRoute('app_auth');
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        $session->remove('forgot_code');
        $session->remove('forgot_email');

        $this->addFlash('success', 'Password reset successfully! Please login with your new password.');
        return $this->redirectToRoute('app_auth');
    }

    #[Route('/logout', name: 'app_logout')]
    public function signout(SessionInterface $session)
    {
        $session->clear();
        return $this->redirectToRoute('app_auth');
    }
}
