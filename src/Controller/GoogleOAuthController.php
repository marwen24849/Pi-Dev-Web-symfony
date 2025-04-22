<?php

namespace App\Controller;

use App\Service\GoogleCalendarService; // Import the service
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleOAuthController extends AbstractController
{
    #[Route('/google/oauth2callback', name: 'google_oauth2callback')]
    public function handleCallback(Request $request, GoogleCalendarService $calendarService): Response
    {
        $code = $request->query->get('code');
        $error = $request->query->get('error');

        if ($error) {
            $this->addFlash('error', 'Google authorization failed: ' . $error);
            // Redirect to a relevant page, maybe user profile or dashboard
            return $this->redirectToRoute('app_home'); // Adjust route name
        }

        if (!$code) {
            $this->addFlash('error', 'Invalid response from Google authorization.');
            return $this->redirectToRoute('app_home'); // Adjust route name
        }

        if ($calendarService->handleCallback($code)) {
            $this->addFlash('success', 'Successfully authorized with Google Calendar.');
            // Redirect user back to where they started, or dashboard
            // Ideally, store the original intended URL in the session before redirecting to Google
            $targetPath = $request->getSession()->remove('_google_auth_target_path') ?? $this->generateUrl('app_home'); // Adjust route name
            return $this->redirect($targetPath);
        } else {
            $this->addFlash('error', 'Failed to process Google authorization callback.');
            return $this->redirectToRoute('app_home'); // Adjust route name
        }
    }
}