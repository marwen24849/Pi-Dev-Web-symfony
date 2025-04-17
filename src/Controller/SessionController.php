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
    $session->setLink('');
    $session->setSalle('');

    $form = $this->createForm(SessionType::class, $session);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $isOnline = $form->get('is_online')->getData() ?? false;
        $session->setIs_online($isOnline);

        // Get date and salle from form
        $date = $form->get('date')->getData();
        $salle = $form->get('salle')->getData();

        // Required field check
        if (!$isOnline && (empty($salle) || $date === null)) {
            $this->addFlash('error', 'Tous les champs doivent être remplis.');
            return $this->render('session/new.html.twig', [
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }

        // Validate salle number
        if (!$isOnline && (!is_numeric($salle) || (int)$salle <= 0)) {
            $this->addFlash('error', 'La salle doit être un nombre entier positif.');
            return $this->render('session/new.html.twig', [
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }
        

        // Conflict check: same salle and date
        if (!$isOnline) {
            $conflict = $entityManager->getRepository(Session::class)->createQueryBuilder('s')
                ->select('count(s.id)')
                ->where('s.date = :date')
                ->andWhere('s.salle = :salle')
                ->setParameter('date', $date)
                ->setParameter('salle', $salle)
                ->getQuery()
                ->getSingleScalarResult();

            if ($conflict > 0) {
                $this->addFlash('error', 'Une session avec la même date et salle existe déjà.');
                return $this->render('session/new.html.twig', [
                    'form' => $form->createView(),
                    'formation' => $formation,
                ]);
            }
        }

        // Max session check based on duration
        $sessionCount = $entityManager->getRepository(Session::class)->count([
            'formation_id' => $formation,
        ]);

        if ($sessionCount >= $formation->getDuration()) {
            $this->addFlash('error', 'Le nombre maximum de sessions pour cette formation est atteint.');
            return $this->render('session/new.html.twig', [
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }

        // Link generation or reset depending on session type
        if ($isOnline) {
            $session->setSalle(null);
            if (empty(trim($session->getLink()))) {
                $session->setLink('online-session-' . uniqid());
            }
        } else {
            $session->setLink('');
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
    $formation = $session->getFormation_id();

    // Ensure default values for link and salle
    if ($session->getLink() === null) {
        $session->setLink('');
    }
    if ($session->getSalle() === null) {
        $session->setSalle('');
    }

    $form = $this->createForm(SessionType::class, $session);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $isOnline = $form->get('is_online')->getData() ?? false;
        $session->setIs_online($isOnline);

        // Get date and salle from form
        $date = $form->get('date')->getData();
        $salle = $form->get('salle')->getData();

        // Required field check
        if (!$isOnline && (empty($salle) || $date === null)) {
            $this->addFlash('error', 'All fields must be filled in.');
            return $this->render('session/edit.html.twig', [
                'session' => $session,
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }

        // Validate salle number
        if (!$isOnline && (!is_numeric($salle) || (int)$salle <= 0)) {
            $this->addFlash('error', 'La salle doit être un nombre entier positif.');
            return $this->render('session/edit.html.twig', [
                'session' => $session,
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }

        // Conflict check: same salle and date (excluding current session)
        if (!$isOnline) {
            $conflict = $entityManager->getRepository(Session::class)->createQueryBuilder('s')
                ->select('count(s.id)')
                ->where('s.date = :date')
                ->andWhere('s.salle = :salle')
                ->andWhere('s.id != :currentId')
                ->setParameter('date', $date)
                ->setParameter('salle', $salle)
                ->setParameter('currentId', $session->getId())
                ->getQuery()
                ->getSingleScalarResult();

            if ($conflict > 0) {
                $this->addFlash('error', 'Une session avec la même date et salle existe déjà.');
                return $this->render('session/edit.html.twig', [
                    'session' => $session,
                    'form' => $form->createView(),
                    'formation' => $formation,
                ]);
            }
        }

        // Max session check based on duration (only if creating a new session for the formation)
        // Note: This check might not be needed for edit since we're modifying an existing session
        // If you want to keep it, you might need to adjust the logic
        $sessionCount = $entityManager->getRepository(Session::class)->count([
            'formation_id' => $formation,
        ]);

        if ($sessionCount > $formation->getDuration()) {
            $this->addFlash('error', 'The maximum number of sessions for this training is reached.');
            return $this->render('session/edit.html.twig', [
                'session' => $session,
                'form' => $form->createView(),
                'formation' => $formation,
            ]);
        }

        // Link generation or reset depending on session type
        if ($isOnline) {
            $session->setSalle(null);
            if (empty(trim($session->getLink()))) {
                $session->setLink('online-session-' . uniqid());
            }
        } else {
            $session->setLink('');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_session_index', [
            'formation_id' => $formation->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    return $this->render('session/edit.html.twig', [
        'session' => $session,
        'formation' => $formation,
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