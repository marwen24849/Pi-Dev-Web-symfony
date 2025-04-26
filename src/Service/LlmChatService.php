<?php
namespace App\Service;

use App\Entity\Chat;
use App\Repository\ChatRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LlmChatService
{
    private $httpClient;
    private $apiKey;
    private $apiUrl;
    private  $chatRepository;
    private $model;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params, ChatRepository $chatRepository)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('together_api_key');
        $this->apiUrl = $params->get('together_api_url');
        $this->chatRepository= $chatRepository;
        $this->model = 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo';
    }


    public function getChats(string $userId){
       return $this->chatRepository->findUserChatHistory($userId);
    }

    public function sendMessage(string $userId, string $userMessage): string
    {
        try {
            $chatHistory = $this->chatRepository->findUserChatHistory($userId);



            $messages = array_map(function(Chat $chat) {
                return [
                    'role' => $chat->getRole(),
                    'content' => $chat->getContent()
                ];
            }, $chatHistory);

            // Add current user message
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $userChat = new Chat();
            $userChat->setUser_id($userId)
                ->setRole('user')
                ->setTimestamp(new \DateTimeImmutable())
                ->setContent($userMessage);
            $this->chatRepository->save($userChat);



            // Call LLM API
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => 200,
                ],
            ]);

            $data = $response->toArray();
            $assistantResponse = $data['choices'][0]['message']['content'];

            // Save assistant response to DB
            $assistantChat = new Chat();
            $assistantChat->setUser_id($userId)
                ->setRole('assistant')
                ->setTimestamp(new \DateTimeImmutable())
                ->setContent($assistantResponse);
            $this->chatRepository->save($assistantChat);

            return $assistantResponse;

        } catch (\Exception $e) {
            // Log error
            error_log($e->getMessage());


            return 'Erreur LLM Chat: ' . $e->getMessage();
        }
    }
}