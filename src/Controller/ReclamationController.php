<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Address;

final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationFormType::class, $reclamation);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            if ($form->isValid()) {
                $reclamation->setStatut("En attente");
                $reclamation->setDate_creation(new \DateTime());

                $session = $request->getSession();
                $userId = $session->get('user_id');
                if ($userId) {
                    $user = $em->getRepository(User::class)->find($userId);
                    if ($user) {
                        $reclamation->setUser_id($user);
                    } else {
                        return new JsonResponse([
                            'status'  => 'error',
                            'message' => 'User not found in database',
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'status'  => 'error',
                        'message' => 'No user found in session',
                    ], 400);
                }

                $em->persist($reclamation);
                $em->flush();
                
                $userEmail = $reclamation->getUser_id()->getEmail();
                $emailMessage = (new Email())
                    ->from(new Address($_ENV['MAILER_FROM'],'Equipe Rh'))
                    ->to($userEmail)
                    ->subject('Reclamation Confirmation')
                    ->html("
                        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; text-align: center;'>
                            <div style='max-width: 500px; margin: auto; background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
                                <h2 style='color: #333333;'>Reclamation Submitted</h2>
                                <p style='font-size: 16px; color: #666666;'>Hello! Your reclamation has been successfully submitted. We will get back to you shortly.</p>
                                <hr style='margin: 30px 0;'>
                                <p style='font-size: 12px; color: #cccccc;'>If you didn’t request this, please ignore this email.</p>
                            </div>
                        </div>
                    ");
                
                $mailer->send($emailMessage);
                
                return new JsonResponse([
                    'status'  => 'success',
                    'message' => 'Reclamation submitted successfully',
                ]);
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return new JsonResponse([
                    'status'  => 'error',
                    'message' => implode(', ', $errors),
                ], 400);
            }
        }

        return $this->render('reclamation/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reclamation/generate', name: 'app_reclamation_generate', methods: ['POST'])]
    public function generate(Request $request, HttpClientInterface $client): JsonResponse
    {
        $sujet = $request->request->get('sujet');
        if (!$sujet) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Le champ sujet est vide',
            ], 400);
        }

        $prompt = "Je vais soumettre une réclamation via l'application de l'entreprise. Peux-tu rédiger un message professionnel et poli en français basé sur le texte suivant : " . $sujet . " ? Assure-toi qu'il soit clair, bien structuré et adapté à une communication formelle. Réponds uniquement avec la réclamation que je vais envoyer, sans aucune explication préalable. Ne me donne que la réclamation et évite toute répétition.";

        $apiKey = $_ENV['HUGGINGFACE_API_KEY'];
        $model = "mistralai/Mistral-7B-Instruct-v0.3";
        $url = "https://api-inference.huggingface.co/models/" . $model;

        $payload = [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens'  => 512,
                'temperature'     => 0.7,
                'return_full_text'=> false,
            ],
        ];

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json'
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Hugging Face API error: ' . $statusCode,
                ], $statusCode);
            }

            $content = $response->getContent();
            $jsonResponse = json_decode($content, true);
            if (isset($jsonResponse[0]['generated_text'])) {
                $generatedText = $jsonResponse[0]['generated_text'];
                $finalText = str_replace($prompt, '', $generatedText);
                $finalText = trim($finalText);
                return new JsonResponse([
                    'status' => 'success',
                    'generatedText' => $finalText,
                ]);
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Réponse invalide de Hugging Face',
                ], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
