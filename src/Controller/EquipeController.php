<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route(name: 'app_equipe_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $equipes = $entityManager
            ->getRepository(Equipe::class)
            ->findAll();

        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/add-members', name: 'app_equipe_add_members', methods: ['GET', 'POST'])]
    public function addMembers(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $availableUsers = $entityManager->getRepository(User::class)->findBy(['id_equipe' => null]);

        if ($request->isMethod('POST')) {
            $selectedUserIds = $request->request->all()['selected_users'] ?? [];
            foreach ($selectedUserIds as $userId) {
                $user = $entityManager->getRepository(User::class)->find($userId);
                if ($user) {
                    $user->setId_equipe($equipe);
                }
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
        }

        return $this->render('equipe/add_members.html.twig', [
            'equipe' => $equipe,
            'availableUsers' => $availableUsers,
        ]);
    }
    #[Route('/{id}/remove-member/{userId}', name: 'app_equipe_remove_member', methods: ['POST'])]
    public function removeMember(Equipe $equipe, int $userId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($userId);
        // Vérifie si l'utilisateur appartient bien à l'équipe
        if (!$user || ($user->getId_equipe() && $user->getId_equipe()->getId() !== $equipe->getId())) {
            $this->addFlash('error', 'Utilisateur non trouvé dans cette équipe.');
            return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
        }

        // Validation du token CSRF
        if ($this->isCsrfTokenValid('remove_member' . $user->getId(), $request->request->get('_token'))) {
            $user->setId_equipe(null);
            $entityManager->flush();
            $this->addFlash('success', 'Membre supprimé avec succès.');
        }

        return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
    }
}
