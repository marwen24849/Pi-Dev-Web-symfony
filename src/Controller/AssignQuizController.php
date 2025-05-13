<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\User_quiz;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssignQuizController extends AbstractController
{
    #[Route('/quiz/{id}/assign', name: 'quiz_assign')]
    public function assignQuiz(Quiz $quiz, Request $request, UserRepository $userRepo): Response
    {
        $searchTerm = $request->query->get('search', '');
        $users = $userRepo->searchUsers($searchTerm);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'content' => $this->renderView('assign_quiz/_user_list.html.twig', [
                    'users' => $users,
                    'quiz' => $quiz
                ])
            ]);
        }

        return $this->render('assign_quiz/index.html.twig', [
            'quiz' => $quiz,
            'users' => $users,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/quiz/{quizId}/assign/{userId}', name: 'quiz_assign_user', methods: ['POST'])]
    public function assignToUser(
        int $quizId,
        int $userId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $quiz = $em->getRepository(Quiz::class)->find($quizId);
        $user = $em->getRepository(User::class)->find($userId);

        if (!$quiz || !$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Quiz ou utilisateur non trouvé'], 404);
            }
            $this->addFlash('error', 'Quiz ou utilisateur non trouvé');
            return $this->redirectToRoute('quiz_assign', ['id' => $quizId]);
        }

        $existingAssignment = $em->getRepository(User_quiz::class)->findOneBy([
            'quiz_id' => $quiz,
            'user_id' => $user
        ]);

        if ($existingAssignment) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Cet utilisateur a déjà ce quiz'], 400);
            }
            $this->addFlash('warning', 'Cet utilisateur a déjà ce quiz.');
            return $this->redirectToRoute('quiz_assign', ['id' => $quizId]);
        }

        $userQuiz = new User_quiz();
        $userQuiz->setQuiz_id($quiz);
        $userQuiz->setUser_id($user);


        $em->persist($userQuiz);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Quiz affecté avec succès à ' . $user->getFirstName() . ' ' . $user->getLastName()
            ]);
        }

        $this->addFlash('success', 'Quiz affecté avec succès à ' . $user->getFirstName() . ' ' . $user->getLastName());
        return $this->redirectToRoute('quiz_assign', ['id' => $quizId]);
    }
}