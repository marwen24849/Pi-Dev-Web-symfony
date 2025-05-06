<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use App\Repository\User_quizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserQuizzesController extends AbstractController
{

    private $quizRepository;
    private $userQuizRepository;

    public function __construct(

        QuizRepository $quizRepository,
        User_quizRepository $userQuizRepository
    ) {

        $this->quizRepository = $quizRepository;
        $this->userQuizRepository = $userQuizRepository;
    }


    #[Route("/mes-quiz", name: 'user_quizzes')]
    public function index(): Response
    {


        $quizzes = $this->userQuizRepository->findAvailableQuizzesForUser(1);

        return $this->render('user_quizzes/index.html.twig', [
            'quizzes' => $quizzes,
            'page_title' => 'Mes Quiz Assignés'
        ]);
    }
}