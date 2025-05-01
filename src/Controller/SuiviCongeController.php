<?php

namespace App\Controller;

use App\Entity\DemandeConge;
use App\Entity\Conge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuiviCongeController extends AbstractController
{
    #[Route('/suivi-conges', name: 'app_suivi_conges')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder au suivi.');
        }

        $conges = $em->getRepository(Conge::class)->createQueryBuilder('c')
            ->join('c.conge_id', 'd')
            ->where('c.user_id = :userId')
            ->andWhere('d.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', 'APPROVED')
            ->getQuery()
            ->getResult();

        return $this->render('suivi_conge/index.html.twig', [
            'conges' => $conges,
            'today' => new \DateTime(),
        ]);
    }

    #[Route('/admin/conge-stats', name: 'admin_conge_stats')]
    public function congeStats(EntityManagerInterface $em): Response
    {
        $conges = $em->getRepository(Conge::class)->findAll();

        return $this->render('suivi_conge/conge_stats.html.twig', [
            'conges' => $conges,
            'today' => new \DateTime(),
        ]);
    }

    #[Route('/admin/conge-line-chart', name: 'admin_conge_line_chart')]
    public function lineChart(EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(DemandeConge::class);
        $conges = $repository->findBy(['status' => 'APPROVED']);

        $chartData = [];

        foreach ($conges as $demande) {
            $user = $demande->getUser_id();
            $name = $user->getFirstName() . ' ' . $user->getLastName();
            $day = $demande->getDateDebut()->format('Y-m-d');

            if (!isset($chartData[$name])) {
                $chartData[$name] = [];
            }

            if (!isset($chartData[$name][$day])) {
                $chartData[$name][$day] = 0;
            }

            $chartData[$name][$day]++;
        }

        return $this->render('suivi_conge/conge_line_chart.html.twig', [
            'chartData' => $chartData
        ]);
    }
}
