<?php

// src/Service/SqlGeneratorService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SqlGeneratorService
{
    private $httpClient;
    private $apiKey;
    private $apiUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = "01301ddd79db215d207cd1e4f3a6c11ef9847f64f10dba8fb5e7aeae94106a38";
        $this->apiUrl = 'https://api.together.xyz/v1/chat/completions';
    }

    public function generateSqlQuery(string $question, array $schemaInfo): string
    {
        $schemaText = $this->buildSchemaText($schemaInfo);

        $prompt = $this->buildPrompt($question, $schemaText);

        $response = $this->sendToTogetherApi($prompt);

        return $this->extractSqlFromResponse($response);
    }

    private function buildSchemaText(array $schemaInfo): string
    {
        $schemaText = '';
        foreach ($schemaInfo as $tableName => $columns) {
            $schemaText .= "- Table `{$tableName}`: ";
            foreach ($columns as $column) {
                $schemaText .= "{$column['name']} ({$column['type']}), ";
            }
            $schemaText .= "\n";
        }
        return $schemaText;
    }

    private function buildPrompt(string $question, string $schemaText): array
    {
        return [
            'model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo-128K',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "Tu es un assistant expert en SQL et spécialisé dans MySQL 8.0.27. " .
                        "Ta tâche est de comprendre des questions formulées en langage naturel, " .
                        "d'analyser la structure des tables, et de générer des requêtes SQL précises, efficaces et prêtes à être exécutées. " .
                        "Tu dois toujours te baser uniquement sur le schéma fourni."
                ],
                [
                    'role' => 'user',
                    'content' => sprintf(
                        "Base de données : SGRH\n\n" .
                        "Voici le schéma des tables disponibles :\n%s\n\n" .
                        "À partir de la question suivante, génère une requête SQL parfaitement adaptée à MySQL 8.0.27.\n\n" .
                        "**Règles importantes :**\n" .
                        "1. Utilise uniquement les tables et colonnes présentes dans le schéma.\n" .
                        "2. Respecte les jointures logiques basées sur les clés (foreign keys si identifiable).\n" .
                        "3. Optimise la lisibilité de la requête (retours à la ligne, indentation).\n" .
                        "4. Ne retourne que la requête SQL, formatée entre ```sql et ```.\n" .
                        "5. Ne fournis aucun commentaire ou explication dans la réponse.\n\n" .
                        "Question : \"%s\"",
                        $schemaText,
                        $question
                    )
                ]
            ]
        ];
    }

    private function sendToTogetherApi(array $prompt): array
    {
        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $prompt
        ]);

        return $response->toArray();
    }

    private function extractSqlFromResponse(array $response): string
    {
        $content = $response['choices'][0]['message']['content'];

        if (preg_match('/```sql(.*?)```/s', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Erreur : Impossible d\'extraire la requête SQL.';
    }
}