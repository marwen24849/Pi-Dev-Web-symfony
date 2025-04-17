<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\User;
use App\Entity\Formation_user;


#[Route('/formation/user')]
final class FormationUserController extends AbstractController
{
    #[Route(name: 'app_formation_user', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $formations = $entityManager
            ->getRepository(Formation::class)
            ->findAll();

        return $this->render('formation/front/index.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/enroll/{id}', name: 'app_formation_enroll', methods: ['GET'])]
public function enroll(
    Formation $formation,
    EntityManagerInterface $entityManager,
    SessionInterface $session,
    Request $request // ✅ ADD THIS
): Response {
    $userId = $session->get('user_id');
    if (!$userId) {
        throw $this->createAccessDeniedException('You must be logged in to enroll.');
    }

    $user = $entityManager->getRepository(User::class)->find($userId);
    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    $existing = $entityManager->getRepository(Formation_user::class)->findOneBy([
        'formation_id' => $formation,
        'user_id' => $user,
    ]);

    if ($existing) {
        $this->addFlash('info', 'Vous êtes déjà inscrit dans cette formation.');
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_formation_index'));
    }

    $enrollment = new Formation_user();
    $enrollment->setFormation_id($formation);
    $enrollment->setUser_id($user);

    $entityManager->persist($enrollment);
    $entityManager->flush();

    $this->addFlash('success', 'Vous vous êtes inscrit avec succès!');

    return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_formation_index'));
}

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/front/index.html.twig', [
            'formation' => $formation,
        ]);
    }

    

    
}
