<?php

// src/Controller/UserResultatController.php
namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Response;
use App\Entity\Resultat;
use App\Repository\QuizRepository;
use App\Repository\ResponseRepository;
use App\Repository\ResultatRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;


class UserResultatController extends AbstractController
{

    private QuizRepository $quizRepository;
    private ResponseRepository $responseRepository;
    private ResultatRepository $resultatRepository;

    public function __construct(

        QuizRepository $quizRepository,
        ResponseRepository $responseRepository,
        ResultatRepository $resultatRepository
    ) {

        $this->quizRepository = $quizRepository;
        $this->responseRepository = $responseRepository;
        $this->resultatRepository = $resultatRepository;
    }

    #[Route('quiz/results', name: 'user_quiz_results')]
    public function index(UserRepository $userRepository): HttpResponse
    {

        $user = $userRepository->find(1);

        $responses = $this->responseRepository->findBy(['user_id' => $user]);

        $quizResultats = [];

        foreach ($responses as $response) {
            $quiz = $response->getQuiz_id();
            $resultat = $response->getResultat_id();

            $quizResultats[] = [
                'quiz' => $quiz,
                'result' => $resultat,
                'color' => $this->getDifficultyColor($quiz->getDifficultylevel())
            ];
        }

        return $this->render('user_resultat/index.html.twig', [
            'quizResultats' => $quizResultats,
        ]);
    }
    #[Route('/user/quiz/{id}/result', name: 'user_quiz_result_details')]
    public function showQuizResult(int $id, UserRepository $userRepository): HttpResponse
    {
        $user = $userRepository->find(1);


        // Vérifier que l'utilisateur a bien passé ce quiz
        $response = $this->responseRepository->findOneBy([
            'user_id' => $user,
            'quiz_id' => $id
        ]);

        if (!$response) {
            throw $this->createNotFoundException('Résultat non trouvé ou non autorisé');
        }

        $resultat = $response->getResultat_id();
        $responses = $response->getResponseResponsess();

        return $this->render('user_resultat/result_details.html.twig', [
            'quiz' => $response->getQuiz_id(),
            'resultat' => $resultat,
            'responses' => $responses,
        ]);
    }

    private function getDifficultyColor(string $difficulty): string
    {
        $difficulty = strtolower($difficulty);
        switch ($difficulty) {
            case 'facile': return '#2ecc71';
            case 'moyen': return '#f1c40f';
            case 'difficile': return '#e74c3c';
            default: return '#7f8c8d';
        }
    }

}