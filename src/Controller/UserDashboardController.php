<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\DemandeConge;
use App\Entity\Formation;
use App\Entity\Reclamation;
use App\Entity\Quiz;


class UserDashboardController extends AbstractController
{


    #[Route('/dashboard', name: 'app_user_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté (remplacer 1 par l'ID dynamique en production)
        $user = $em->getRepository(User::class)->find(1);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Statistiques des congés
        $conges = $em->getRepository(DemandeConge::class)->findBy(['user_id' => $user]);
        $congesApprouves = array_filter($conges, fn($c) => $c->getStatus() === 'approved');
        $joursCongesPris = array_sum(array_map(fn($c) => $c->getDateDebut()->diff($c->getDateFin())->days, $congesApprouves));

        // Formations
        $formations = $em->getRepository(Formation::class)->findAll();
        $formationsCompletees = 3; // À remplacer par la logique réelle

        // Activités récentes
        $recentActivities = [
            [
                'type' => 'conge',
                'title' => 'Demande de congé soumise',
                'date' => '15-20 Juillet 2023',
                'status' => 'pending'
            ],
            [
                'type' => 'formation',
                'title' => 'Formation complétée',
                'description' => 'Sécurité informatique',
                'status' => 'approved'
            ],
            [
                'type' => 'quiz',
                'title' => 'Quiz passé',
                'description' => 'PHP avancé - Score: 85%',
                'status' => 'approved'
            ],
            [
                'type' => 'reclamation',
                'title' => 'Réclamation traitée',
                'description' => 'Problème de matériel',
                'status' => 'approved'
            ]
        ];

        return $this->render('user_dashboard/index.html.twig', [
            'user' => $user,
            'congesTotal' => 24, // Total de jours de congé annuels
            'congesPris' => $joursCongesPris,
            'formationsTotal' => count($formations),
            'formationsCompletees' => $formationsCompletees,
            'recentActivities' => $recentActivities,
            'performanceData' => [
                'labels' => ['Symfony', 'React', 'Gestion de projet', 'Sécurité', 'DevOps'],
                'scores' => [85, 70, 60, 90, 40]
            ]
        ]);
    }

}