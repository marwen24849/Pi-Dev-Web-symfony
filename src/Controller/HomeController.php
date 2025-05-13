<?php

namespace App\Controller;

use App\Repository\Demande_congeRepository;
use App\Repository\ProjetRepository;
use App\Repository\QuizRepository;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;





final class HomeController extends AbstractController
{


    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private QuizRepository $quizRepo,
        private ReclamationRepository $reclamationRepo,
        private Demande_congeRepository $congeRepo,
        private ProjetRepository $projetRepo
    ) {
    }

    #[Route('/user', name: 'home')]
    public function userUi(): Response
    {
        return $this->render('front.html.twig');
    }
    #[Route('/', name: 'test')]
    public function login(): Response
    {
        return $this->redirectToRoute('app_auth');
    }


    #[Route('/home', name: 'app_homepage')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $userStats = $this->userRepo->getUserStatsByRole();
        $userRegistrations = $this->userRepo->getMonthlyRegistrations();
        $quizStats = $this->quizRepo->getQuizStats();
        $reclamationStats = $this->reclamationRepo->getReclamationStats();
        $congeStats = $this->congeRepo->getCongeStats();
        $projetStats = $this->projetRepo->getProjetStatusStats();

        return $this->render('home/index.html.twig', [
            'userStats' => $userStats,
            'userRegistrations' => $userRegistrations,
            'quizStats' => $quizStats,
            'reclamationStats' => $reclamationStats,
            'congeStats' => $congeStats,
            'projetStats' => $projetStats,
        ]);
    }




    private function createUserRolesChart(ChartBuilderInterface $chartBuilder, array $userStats): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $labels = [];
        $data = [];
        foreach ($userStats as $stat) {
            $labels[] = $stat['role'];
            $data[] = $stat['count'];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Utilisateurs',
                    'backgroundColor' => '#4361ee',
                    'borderColor' => '#4361ee',
                    'data' => $data,
                    'borderRadius' => 6,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ]);

        return $chart;
    }


    private function createUserRegistrationsChart(ChartBuilderInterface $chartBuilder): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            'datasets' => [
                [
                    'label' => 'Inscriptions',
                    'data' => [12, 19, 3, 5, 2, 3, 7, 10, 15, 8, 4, 6],
                    'borderColor' => '#4361ee',
                    'backgroundColor' => 'rgba(67, 97, 238, 0.1)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }

    private function createReclamationStatusChart(ChartBuilderInterface $chartBuilder, array $reclamationStats): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $labels = [];
        $data = [];
        foreach ($reclamationStats as $stat) {
            $labels[] = $stat['statut'];
            $data[] = $stat['count'];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#4361ee', '#3a0ca3', '#7209b7'],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }

    private function createProjectStatusChart(ChartBuilderInterface $chartBuilder, array $projetStats): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);

        $labels = [];
        $data = [];
        foreach ($projetStats as $stat) {
            $labels[] = $stat['status'];
            $data[] = $stat['count'];
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#4361ee', '#3a0ca3', '#7209b7'],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }
}
