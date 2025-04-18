<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404161604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE department CHANGE total_equipe total_equipe BIGINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD equipe_id BIGINT DEFAULT NULL, DROP equipe
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
            ALTER TABLE department CHANGE total_equipe total_equipe BIGINT DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet DROP FOREIGN KEY FK_50159CA96D861B89
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_50159CA96D861B89 ON projet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD equipe VARCHAR(255) NOT NULL, DROP equipe_id
        SQL);
    }
}
