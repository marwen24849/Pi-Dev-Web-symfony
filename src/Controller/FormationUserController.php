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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;


#[Route('/formation/user')]
final class FormationUserController extends AbstractController
{
    #[Route(name: 'app_formation_user', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response

    {

        $search = $request->query->get('search');
        $queryBuilder = $entityManager->getRepository(Formation::class)->createQueryBuilder('f');

        if ($search) {
            $queryBuilder->where('f.title LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        $formations = $queryBuilder->getQuery()->getResult();

        foreach ($formations as $formation) {
            $enrolledCount = $entityManager->getRepository(Formation_user::class)
                ->count(['formation_id' => $formation]);

            $formation->spotsLeft = $formation->getCapacity() - $enrolledCount;
        }

        return $this->render('formation/front/index.html.twig', [
            'formations' => $formations,
            'search' => $search,
        ]);
    }

    #[Route('/list', name: 'app_formation_user_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $formations = $entityManager->getRepository(Formation::class)->findAll();

        $formationData = [];
        foreach ($formations as $formation) {
            $enrollments = $entityManager->getRepository(Formation_user::class)->findBy([
                'formation_id' => $formation
            ]);

            $users = array_map(function($enrollment) {
                return $enrollment->getUser_id();
            }, $enrollments);

            $formationData[] = [
                'formation' => $formation,
                'users' => $users
            ];
        }

        return $this->render('formation/formationUserList.html.twig', [
            'formationData' => $formationData,
        ]);
    }

    #[Route('/enroll/{id}', name: 'app_formation_enroll', methods: ['GET'])]
public function enroll(
    Formation $formation,
    EntityManagerInterface $entityManager,
    SessionInterface $session,
    Request $request,
    MailerInterface $mailer // 👈 Injected here
): Response {
    $userId = $session->get('user_id');
    if (!$userId) {
        throw $this->createAccessDeniedException('You must be logged in to enroll.');
    }

    $user = $entityManager->getRepository(User::class)->find($userId);
    if (!$user) {
        throw $this->createNotFoundException('User not found.');
    }

    $enrolledCount = $entityManager->getRepository(Formation_user::class)
        ->count(['formation_id' => $formation]);

    $spotsLeft = $formation->getCapacity() - $enrolledCount;

    if ($spotsLeft <= 0) {
        $this->addFlash('danger', 'Désolé, il n\'y a plus de places disponibles pour cette formation.');
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_formation_user'));
    }

    $existing = $entityManager->getRepository(Formation_user::class)->findOneBy([
        'formation_id' => $formation,
        'user_id' => $user,
    ]);

    if ($existing) {
        $this->addFlash('info', 'Vous êtes déjà inscrit dans cette formation.');
    } else {
        $enrollment = new Formation_user();
        $enrollment->setFormation_id($formation);
        $enrollment->setUser_id($user);
        $entityManager->persist($enrollment);
        $entityManager->flush();

        $this->addFlash('success', 'Vous vous êtes inscrit avec succès! Veuillez vérifier votre courrier.');

        // ✅ SEND EMAIL HERE
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@yourapp.com', 'Formation Team'))
            ->to($user->getEmail())
            ->subject('Confirmation d\'inscription à la formation: ' . $formation->getTitle())
            ->htmlTemplate('formation/front/enrollment.html.twig')
            ->context([
                'formation' => $formation,
                'sessions' => $formation->getSessions(), // Make sure this is loaded
            ]);

        $mailer->send($email);
    }

    return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_formation_user'));
}



    #[Route('/details/{id}', name: 'app_formation_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/front/index.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/ban/{formation_id}/{user_id}', name: 'app_formation_user_ban', methods: ['POST'])]
    public function banUser(
        int $formation_id,
        int $user_id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $formation = $entityManager->getRepository(Formation::class)->find($formation_id);
        $user = $entityManager->getRepository(User::class)->find($user_id);

        if (!$formation || !$user) {
            throw $this->createNotFoundException('Formation ou utilisateur introuvable.');
        }

        $enrollment = $entityManager->getRepository(Formation_user::class)->findOneBy([
            'formation_id' => $formation,
            'user_id' => $user,
        ]);

        if ($enrollment) {
            $entityManager->remove($enrollment);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur retiré avec succès de la formation.');
        } else {
            $this->addFlash('danger', 'L’utilisateur n’est pas inscrit à cette formation.');
        }

        return $this->redirectToRoute('app_formation_user_list');
    }
}
