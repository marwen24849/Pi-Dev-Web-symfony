<?php
namespace App\Controller;

use App\Service\LlmChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatController extends AbstractController
{
    private const SESSION_CHAT_HISTORY = 'chat_history';

    #[Route('/chat', name: 'app_chat')]
    public function index(SessionInterface $session, LlmChatService $chatService): Response
    {
        $history = $chatService->getChats(20);

        return $this->render('chat/index.html.twig', [
            'chatHistory' => $history
        ]);
    }

    #[Route('/api/chat/send', name: 'app_chat_send', methods: ['POST'])]
    public function sendMessage(Request $request, LlmChatService $chatService, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $this->getUser() ? $this->getUser()->getId() : 20;
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            return $this->json(['error' => 'Message is required'], 400);
        }

        // Get current history
        $history = $session->get(self::SESSION_CHAT_HISTORY, []);

        // Add user message to history
        $history[] = [
            'type' => 'user',
            'content' => $message,
            'time' => date('H:i')
        ];

        // Get AI response
        $response = $chatService->sendMessage($userId, $message);

        // Add AI response to history
        $history[] = [
            'type' => 'assistant',
            'content' => $response,
            'time' => date('H:i')
        ];

        // Save updated history
        $session->set(self::SESSION_CHAT_HISTORY, $history);

        return $this->json([
            'response' => $response,
            'history' => $history
        ]);
    }

    #[Route('/api/chat/clear', name: 'app_chat_clear', methods: ['POST'])]
    public function clearHistory(SessionInterface $session): JsonResponse
    {
        $session->set(self::SESSION_CHAT_HISTORY, []);

        return $this->json([
            'success' => true
        ]);
    }
}