<?php

// src/Service/ResultAnalyzerService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResultAnalyzerService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = "01301ddd79db215d207cd1e4f3a6c11ef9847f64f10dba8fb5e7aeae94106a38";
    }

    public function analyzeResults(array $results, string $originalQuestion): string
    {
        $dataSample = json_encode(array_slice($results, 0, 3), JSON_PRETTY_PRINT);

        $prompt = [
            'model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo-128K',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en analyse de données. Analyse les résultats et fournis une réponse claire en langage naturel.'
                ],
                [
                    'role' => 'user',
                    'content' => sprintf(
                        "Question originale : \"%s\"\n\n".
                        "Voici un échantillon des données retournées :\n%s\n\n".
                        "Fournis une analyse claire et concise de ces résultats en français. ",
                        $originalQuestion,
                        $dataSample
                    )
                ]
            ]
        ];

        $response = $this->httpClient->request('POST', 'https://api.together.xyz/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $prompt
        ]);

        $responseData = $response->toArray();

        return $responseData['choices'][0]['message']['content'];
    }
}