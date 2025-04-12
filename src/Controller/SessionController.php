<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Formation;
use App\Form\SessionType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formation/{formation_id}/session')]
final class SessionController extends AbstractController
{
    #[Route('/', name: 'app_session_index', methods: ['GET'])]
    public function index(
        int $formation_id,
        FormationRepository $formationRepository
    ): Response {
        $formation = $formationRepository->find($formation_id);
        
        if (!$formation) {
            throw $this->createNotFoundException('Formation not found');
        }

        return $this->render('session/index.html.twig', [
            'sessions' => $formation->getSessions(),
            'formation' => $formation,
        ]);
    }

    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        int $formation_id,
        FormationRepository $formationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $formation = $formationRepository->find($formation_id);
        if (!$formation) {
            throw $this->createNotFoundException('Formation not found');
        }

        $session = new Session();
        $session->setFormation_id($formation);
        $session->setIs_online(false);
        $session->setLink(''); // Initialize with empty string
        
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isOnline = $form->get('is_online')->getData() ?? false;
            $session->setIs_online($isOnline);
            
            if ($session->getIs_online()) {
                $session->setSalle(null);
                if (empty(trim($session->getLink()))) {
                    $session->setLink('online-session-' . uniqid());
                }
            } else {
                $session->setLink(''); // Set empty string for in-person sessions
            }

            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [
                'formation_id' => $formation->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
            'formation' => $session->getFormation_id(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Session $session,
        EntityManagerInterface $entityManager
    ): Response {
        // Ensure link is never null before form handling
        if ($session->getLink() === null) {
            $session->setLink('');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isOnline = $form->get('is_online')->getData() ?? false;
            $session->setIs_online($isOnline);
            
            if ($session->getIs_online()) {
                $session->setSalle(null);
                if (empty(trim($session->getLink()))) {
                    $session->setLink('online-session-' . uniqid());
                }
            } else {
                $session->setLink(''); // Set empty string for in-person sessions
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [
                'formation_id' => $session->getFormation_id()->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'formation' => $session->getFormation_id(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_session_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Session $session,
        EntityManagerInterface $entityManager
    ): Response {
        $formationId = $session->getFormation_id()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_session_index', [
            'formation_id' => $formationId,
        ], Response::HTTP_SEE_OTHER);
    }
}