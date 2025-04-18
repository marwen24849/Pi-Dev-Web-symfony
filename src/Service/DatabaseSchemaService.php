<?php


namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseSchemaService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getDatabaseSchema(): array
    {
        $schema = [];
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->getSchemaManager();

        $tables = $schemaManager->listTables();

        foreach ($tables as $table) {
            $tableName = $table->getName();
            $columns = [];

            foreach ($table->getColumns() as $column) {
                $columns[] = [
                    'name' => $column->getName(),
                    'type' => $column->getType()->getName(),
                ];
            }

            $schema[$tableName] = $columns;
        }

        return $schema;
    }
}