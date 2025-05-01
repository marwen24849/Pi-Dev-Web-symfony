<?php

namespace App\Controller;

use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

final class ListeReclamationsController extends AbstractController
{
    #[Route('/liste/reclamations', name: 'app_liste_reclamations')]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findBy([], ['date_creation' => 'DESC']);
        return $this->render('liste_reclamations/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/reclamation/{id}/generate-response', name: 'app_generate_reclamation_response', methods: ['POST'])]
    public function generateResponse(
        int $id,
        ReclamationRepository $reclamationRepository,
        HttpClientInterface $client
    ): JsonResponse {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            return new JsonResponse(['status' => 'error', 'message' => 'Réclamation non trouvée'], 404);
        }

        $prompt = "Veuillez rédiger une réponse professionnelle et polie en français pour la réclamation suivante :\n\n"
                . $reclamation->getDescription();

        $apiKey = $_ENV['HUGGINGFACE_API_KEY'];
        $model  = "mistralai/Mistral-7B-Instruct-v0.3";
        $url    = "https://api-inference.huggingface.co/models/$model";

        $payload = [
            'inputs'     => $prompt,
            'parameters' => [
                'max_new_tokens'   => 512,
                'temperature'      => 0.7,
                'return_full_text' => false,
            ],
        ];

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
            if ($response->getStatusCode() !== 200) {
                return new JsonResponse([
                    'status'  => 'error',
                    'message' => 'API Hugging Face erreur : ' . $response->getStatusCode()
                ], $response->getStatusCode());
            }
            $data = $response->toArray();
            if (isset($data[0]['generated_text'])) {
                $text = trim(str_replace($prompt, '', $data[0]['generated_text']));
                return new JsonResponse(['status' => 'success', 'generatedText' => $text]);
            }
            return new JsonResponse(['status' => 'error', 'message' => 'Réponse invalide de l’API'], 500);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/reclamation/{id}/send-response', name: 'app_send_reclamation_response', methods: ['POST'])]
    public function sendResponse(
        int $id,
        ReclamationRepository $reclamationRepository,
        Request $request,
        MailerInterface $mailer
    ): JsonResponse {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            return new JsonResponse(['status' => 'error', 'message' => 'Réclamation non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $responseText = $data['response'] ?? '';
        if (!$responseText) {
            return new JsonResponse(['status' => 'error', 'message' => 'Réponse vide'], 400);
        }

        $userEmail = $reclamation->getUser_id()->getEmail();
        $emailMessage = (new Email())
        ->from(new Address($_ENV['MAILER_FROM'],'Resources Humaines'))
            ->to($userEmail)
            ->subject('Réponse à votre réclamation')
            ->html("
                <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; text-align: center;'>
                    <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #333333;'>Réponse à votre réclamation</h2>
                        <p style='font-size: 16px; color: #666666;'>" . nl2br(htmlspecialchars($responseText)) . "</p>
                        <hr style='margin: 30px 0;'>
                        <p style='font-size: 12px; color: #cccccc;'>Si vous n\'avez pas fait cette demande, vous pouvez ignorer ce message.</p>
                    </div>
                </div>
            ");
        $mailer->send($emailMessage);

        return new JsonResponse(['status' => 'success', 'message' => 'Réponse envoyée.']);
    }
}
