<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserDashController extends AbstractController
{
    #[Route('dash', name: 'app_user_dash')]
    public function index(): Response
    {
        return $this->render('user_dash/index.html.twig', [
            'controller_name' => 'UserDashController',
        ]);
    }
}
