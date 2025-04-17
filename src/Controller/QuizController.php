<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Quiz_questions;
use App\Form\QuizEditType;
use App\Form\QuizType;
use App\Repository\QuestionRepository;
use App\Repository\Quiz_questionsRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'app_quiz_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $quizzes = $em->getRepository(Quiz::class)->findAll();
        $categories = array_unique(array_map(fn($q) => $q->getCategory(), $quizzes));
        return $this->render('quiz/index.html.twig', ['quizzes' => $quizzes,
            'categories' => $categories
        ]);
    }

    #[Route('/quiz/new', name: 'app_quiz_new')]
    public function new(Request $request, EntityManagerInterface $em, QuestionRepository $questionRepository): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz, ['entity_manager' => $em]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionCount = $form->get('questionCount')->getData();
            $category = $quiz->getCategory();
            $difficulty = $quiz->getDifficultylevel();
            $quiz->setPasser(true);

            $questions = $questionRepository->findRandomQuestions($category, $difficulty, $questionCount);
            foreach ($questions as $question) {
                $quizQuestion = new Quiz_questions();
                $quizQuestion->setQuiz_id($quiz);
                $quizQuestion->setQuestions_id($question);
                $em->persist($quizQuestion);
            }

            $em->persist($quiz);
            $em->flush();

            $this->addFlash('success', 'Quiz créé avec succès!');
            return $this->redirectToRoute('app_quiz_index');
        }

        return $this->render('quiz/new.html.twig', ['form' => $form->createView()]);
    }


    #[Route('/quiz/{id}/edit', name: 'quiz_edit', methods: ['POST'])]
    public function updateQuiz(int $id, Request $request, EntityManagerInterface $em, QuizRepository $quizRepository): JsonResponse
    {
        $quiz = $quizRepository->find($id);
        if (!$quiz) {
            return $this->json([
                'status' => 'error',
                'message' => 'Quiz introuvable'
            ], 404);
        }
        $title = $request->request->get('title');
        $minimumSuccessPercentage = $request->request->get('minimumSuccessPercentage');
        $quizTime = $request->request->get('quizTime');

        if (empty($title)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le titre est requis'
            ], 400);
        }

        try {
            $quiz->setTitle($title);
            $quiz->setMinimumSuccessPercentage($minimumSuccessPercentage);
            $quiz->setQuizTime($quizTime);

            $em->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Quiz mis à jour avec succès',
                'quiz' => [
                    'id' => $quiz->getId(),
                    'title' => $quiz->getTitle(),
                    'minimumSuccessPercentage' => $quiz->getMinimumSuccessPercentage(),
                    'quizTime' => $quiz->getQuizTime()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du quiz'
            ], 500);
        }
    }

    #[Route('/quiz/{id}/show', name: 'quiz_details', methods: ['GET'])]
    public function showQuiz(int $id, QuizRepository $quizRepository): Response
    {
        $quiz = $quizRepository->find($id);
        if (!$quiz) {
            return $this->json([
                'status' => 'error',
                'message' => 'Quiz introuvable'
            ], 404);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Quiz mis à jour avec succès',
            'quiz' => [
                'id' => $quiz->getId(),
                'title' => $quiz->getTitle(),
                'category' => $quiz->getCategory(),
                'minimumSuccessPercentage' => $quiz->getMinimumSuccessPercentage(),
                'quizTime' => $quiz->getQuizTime()
            ]
        ]);


    }

    #[Route('/quiz/{quizId}/delete', name: 'quiz_delete', methods: ['POST'])]
    public function deleteQuiz(int $quizId, EntityManagerInterface $em, QuizRepository $quizRepository): Response
    {
        $quiz = $quizRepository->find($quizId);
        if (!$quiz) {
            return $this->json(['status' => 'error', 'message' => 'Quiz introuvable'], 404);
        }
        $em->remove($quiz);
        $em->flush();
        return $this->json(['status' => 'success', 'message' => 'Quiz supprimé avec succès']);
    }

    #[Route('/quiz/{quizId}/question/{questionId}/delete', name: 'quiz_question_delete', methods: ['DELETE'])]
    public function deleteQuestion(int $quizId, int $questionId, EntityManagerInterface $em, Quiz_questionsRepository $quizQuestionsRepository): Response
    {
        $quizQuestion = $quizQuestionsRepository->findOneBy(['quiz_id' => $quizId, 'questions_id' => $questionId]);
        if (!$quizQuestion) {
            return $this->json(['status' => 'error', 'message' => 'Question introuvable'], 404);
        }
        $em->remove($quizQuestion);
        $em->flush();
        return $this->json(['status' => 'success', 'message' => 'Question supprimée avec succès']);
    }

    #[Route('/quiz/{id}/questions', name: 'quiz_questions', methods: ['GET'])]
    public function getQuestions(int $id, QuizRepository $quizRepository, Quiz_questionsRepository $quizQuestionsRepository): JsonResponse
    {
        $quiz = $quizRepository->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Quiz non trouvé'], 404);
        }

        $quizQuestions = $quizQuestionsRepository->findBy(['quiz_id' => $quiz]);
        $questions = array_map(fn($q) => ['id' => $q->getQuestions_id()->getId(), 'text' => $q->getQuestions_id()->getQuestionTitle()], $quizQuestions);
        return new JsonResponse(['questions' => $questions]);
    }

    #[Route('/quiz/count-questions', name: 'app_quiz_count_questions')]
    public function countQuestions(Request $request, QuestionRepository $questionRepository): Response
    {
        $category = $request->query->get('category');
        $difficulty = $request->query->get('difficulty');
        $count = $questionRepository->countByCategoryAndDifficulty($category, $difficulty);
        return $this->json(['count' => $count]);
    }
}
