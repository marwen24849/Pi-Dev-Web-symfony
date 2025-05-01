<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417102512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_conge ADD type_conge LONGTEXT DEFAULT NULL, DROP type_congé, CHANGE autre autre LONGTEXT DEFAULT NULL, CHANGE justification justification LONGTEXT DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL, CHANGE certificate certificate VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD equipe_id BIGINT DEFAULT NULL, DROP equipe, CHANGE responsable responsable VARCHAR(255) DEFAULT NULL, CHANGE date_debut date_debut DATETIME DEFAULT NULL, CHANGE date_fin date_fin DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD CONSTRAINT FK_50159CA96D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_50159CA96D861B89 ON projet (equipe_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_conge ADD type_congé LONGTEXT NOT NULL, DROP type_conge, CHANGE autre autre LONGTEXT NOT NULL, CHANGE justification justification LONGTEXT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE date_debut date_debut DATE NOT NULL, CHANGE date_fin date_fin DATE NOT NULL, CHANGE certificate certificate VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet DROP FOREIGN KEY FK_50159CA96D861B89
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_50159CA96D861B89 ON projet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD equipe VARCHAR(255) NOT NULL, DROP equipe_id, CHANGE responsable responsable VARCHAR(255) NOT NULL, CHANGE date_debut date_debut DATETIME NOT NULL, CHANGE date_fin date_fin DATETIME NOT NULL
        SQL);
    }
}
