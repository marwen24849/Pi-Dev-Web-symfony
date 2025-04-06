<?php
namespace App\Controller;

use App\Entity\Response as QuizResponse;
use App\Entity\Response_responses;
use App\Entity\Resultat;
use App\Repository\QuizRepository;
use App\Repository\ResultatRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizResponseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private QuizRepository $quizRepository;
    private ResultatRepository $resultatRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuizRepository $quizRepository,
        ResultatRepository $resultatRepository
    ) {
        $this->entityManager = $entityManager;
        $this->quizRepository = $quizRepository;
        $this->resultatRepository = $resultatRepository;
    }

    #[Route('/quiz/{id}/start', name: 'quiz_start')]
    public function startQuiz(int $id, Request $request): Response
    {
        $session = $request->getSession();
        $quiz = $this->quizRepository->findWithQuestions($id);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        $session->set('current_quiz', $id);
        $session->set('current_question', 0);
        $session->set('user_answers', []);
        $session->set('quiz_start_time', time());
        $session->set('quiz_time_limit', $quiz->getQuizTime() * 60);
        $session->set('quiz_time_remaining', $quiz->getQuizTime() * 60);

        return $this->redirectToRoute('quiz_question');
    }

    #[Route('/quiz/question', name: 'quiz_question')]
    public function showQuestion(Request $request): Response
    {
        $session = $request->getSession();
        $quizId = $session->get('current_quiz');
        $currentQuestionIndex = $session->get('current_question', 0);
        $quiz = $this->quizRepository->findWithQuestions($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }
        $timeLimit = $session->get('quiz_time_limit');
        $startTime = $session->get('quiz_start_time');
        $elapsedTime = time() - $startTime;
        $timeRemaining = max(0, $timeLimit - $elapsedTime);
        $session->set('quiz_time_remaining', $timeRemaining);


        if ($timeRemaining <= 0) {
            return $this->redirectToRoute('quiz_timeout');
        }

        $questions = $quiz->getQuiz_questionss()->map(function($qq) {
            return $qq;
        })->toArray();

        if ($currentQuestionIndex >= count($questions)) {
            return $this->redirectToRoute('quiz_submit');
        }

        $question = $questions[$currentQuestionIndex];

        return $this->render('quiz/question.html.twig', [
            'quiz' => $quiz,
            'question' => $question,
            'questionNumber' => $currentQuestionIndex + 1,
            'totalQuestions' => count($questions),
            'timeRemaining' => $timeRemaining,
            'timeLimit' => $timeLimit,
        ]);
    }

    #[Route('/quiz/answer', name: 'quiz_answer', methods: ['POST'])]
    public function handleAnswer(Request $request): Response
    {
        $session = $request->getSession();
        $timeLimit = $session->get('quiz_time_limit');
        $startTime = $session->get('quiz_start_time');
        $elapsedTime = time() - $startTime;
        $timeRemaining = max(0, $timeLimit - $elapsedTime);

        if ($timeRemaining <= 0) {
            return $this->redirectToRoute('quiz_timeout');
        }

        $quizId = $session->get('current_quiz');
        $currentQuestionIndex = $session->get('current_question', 0);
        $quiz = $this->quizRepository->findWithQuestions($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        $questions = $quiz->getQuiz_questionss()->map(function($qq) {
            return $qq;
        })->toArray();

        $question = $questions[$currentQuestionIndex];
        $answer = $request->request->get('answer');

        $userAnswers = $session->get('user_answers', []);
        $userAnswers[$question->getQuestions_id()->getId()] = $answer;
        $session->set('user_answers', $userAnswers);

        $session->set('quiz_time_remaining', $timeRemaining);

        $session->set('current_question', $currentQuestionIndex + 1);

        if ($currentQuestionIndex + 1 >= count($questions)) {
            return $this->redirectToRoute('quiz_submit');
        }

        return $this->redirectToRoute('quiz_question');
    }

    #[Route('/quiz/submit', name: 'quiz_submit')]
    public function submitQuiz(Request $request, UserRepository $userRepository): Response
    {
        $session = $request->getSession();
        $quizId = $session->get('current_quiz');
        $userAnswers = $session->get('user_answers', []);
        $quiz = $this->quizRepository->findWithQuestions($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        $totalScore = 0;
        $maxScore = 0;

        foreach ($quiz->getQuiz_questionss() as $qq) {
            $question = $qq->getQuestions_id();
            $maxScore += $question->getScore();

            if (isset($userAnswers[$question->getId()])) {
                if ($userAnswers[$question->getId()] === $question->getRightAnswer()) {
                    $totalScore += $question->getScore();
                }
            }
        }

        $percentage = ($maxScore > 0) ? ($totalScore / $maxScore) * 100 : 0;
        $passed = $percentage >= $quiz->getMinimumSuccessPercentage();

        $resultat = new Resultat();
        $resultat->setScore($totalScore);
        $resultat->setPercentage($percentage);
        $resultat->setResultat($passed);
        $this->entityManager->persist($resultat);

        $response = new QuizResponse();
        $response->setId($this->generateCustomId());
        $response->setQuiz_id($quiz);
        $response->setResultat_id($resultat);
        $response->setUser_id($userRepository->find(1));
        $this->entityManager->persist($response);
        foreach ($userAnswers as $questionId => $answer) {
            $responseResponse = new Response_responses();
            $responseResponse->setResponse_id($response);
            $responseResponse->setAnswer($answer);
            foreach ($quiz->getQuiz_questionss() as $qq) {
                if ($qq->getQuestions_id()->getId() === $questionId) {
                    $responseResponse->setQuestion($qq->getQuestions_id()->getQuestionTitle());
                    break;
                }
            }

            $this->entityManager->persist($responseResponse);
        }
        $this->entityManager->flush();
        $session->remove('current_quiz');
        $session->remove('current_question');
        $session->remove('user_answers');
        $session->remove('quiz_start_time');
        $session->remove('quiz_time_remaining');
        $session->remove('quiz_time_limit');

        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'score' => $totalScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'maxScore' => $maxScore,
        ]);
    }

    #[Route('/quiz/timeout', name: 'quiz_timeout')]
    public function handleTimeout(Request $request): Response
    {
        $session = $request->getSession();
        $quizId = $session->get('current_quiz');
        $quiz = $this->quizRepository->find($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        $session->remove('current_quiz');
        $session->remove('current_question');
        $session->remove('user_answers');
        $session->remove('quiz_start_time');
        $session->remove('quiz_time_remaining');
        $session->remove('quiz_time_limit');

        return $this->render('quiz/timeout.html.twig', [
            'quiz' => $quiz,
            'quizTime' => $quiz->getQuizTime(),
        ]);
    }

    private function generateCustomId(): string
    {
        return hash('sha256', uniqid('prefix_', true));
    }
}