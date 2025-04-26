<?php
// src/Controller/SqlAssistantController.php

namespace App\Controller;

use App\Service\SqlGeneratorService;
use App\Service\ResultAnalyzerService;
use App\Service\DatabaseSchemaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SqlAssistantController extends AbstractController
{
    public function __construct(
        private SqlGeneratorService    $sqlGenerator,
        private ResultAnalyzerService  $resultAnalyzer,
        private DatabaseSchemaService  $schemaService,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/sql-assistant', name: 'sql_assistant')]
    public function index(): Response
    {
        $schema = $this->schemaService->getDatabaseSchema();

        return $this->render('sql_assistant/index.html.twig', [
            'schema' => json_encode($schema, JSON_PRETTY_PRINT),
        ]);
    }

    #[Route('/generate-sql', name: 'generate_sql', methods: ['POST'])]
    public function generateSql(Request $request): JsonResponse
    {
        $question = $request->request->get('question');
        $schema = $this->schemaService->getDatabaseSchema();

        try {
            $sqlQuery = $this->sqlGenerator->generateSqlQuery($question, $schema);

            return $this->json([
                'success' => true,
                'sql' => $sqlQuery,
                'formatted_sql' => $this->formatSql($sqlQuery),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/execute-sql', name: 'execute_sql', methods: ['POST'])]
    public function executeSql(Request $request): JsonResponse
    {
        $sql = $request->request->get('sql');
        $question = $request->request->get('question');

        try {
            $stmt = $this->em->getConnection()->prepare($sql);
            $results = $stmt->executeQuery()->fetchAllAssociative();

            $analysis = $this->resultAnalyzer->analyzeResults($results, $question);

            $this->saveToHistory($question, $sql, $results, $analysis);

            return $this->json([
                'success' => true,
                'results' => $results,
                'analysis' => $analysis,
                'row_count' => count($results),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function saveToHistory(string $question, string $sql, array $results, string $analysis): void
    {
        // Implementation for saving to history would go here
    }

    private function formatSql(string $sql): string
    {
        // Simple SQL formatting for display
        $keywords = ['SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'GROUP BY', 'ORDER BY', 'LIMIT', 'HAVING'];

        foreach ($keywords as $keyword) {
            $sql = str_ireplace($keyword, "\n".$keyword, $sql);
        }

        return trim($sql);
    }
}