<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShowUsersController extends AbstractController
{
    #[Route('/show_users', name: 'app_show_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('show_users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/update-role/{id}', name: 'user_update_role', methods: ['POST'])]
    public function updateRole(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $csrfToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('update-role-' . $user->getId(), $csrfToken)) {
            $newRole = $request->request->get('role');
            if (in_array($newRole, ['USER', 'ADMIN'])) {
                $user->setRole($newRole);
                $em->flush();
            }
        }
        return $this->redirectToRoute('app_show_users');
    }

    #[Route('/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-user-' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_show_users');
    }
}
