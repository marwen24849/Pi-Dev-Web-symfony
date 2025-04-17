<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417082405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE chat (id BIGINT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE conge (id BIGINT AUTO_INCREMENT NOT NULL, conge_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, INDEX IDX_2ED89348CAAC9A59 (conge_id), INDEX IDX_2ED89348A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE demande_conge (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT DEFAULT NULL, type_congé LONGTEXT NOT NULL, autre LONGTEXT NOT NULL, justification LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, certificate VARCHAR(255) NOT NULL, INDEX IDX_D8061061A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE demande_mobilite (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT DEFAULT NULL, destination VARCHAR(255) NOT NULL, motivation LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_84083591A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE department (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, total_equipe BIGINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE equipe (id BIGINT AUTO_INCREMENT NOT NULL, department_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, members BIGINT NOT NULL, INDEX IDX_2449BA15AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE formation (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, duration INT NOT NULL, capacity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE formation_user (formation_id BIGINT NOT NULL, user_id BIGINT NOT NULL, INDEX IDX_DA4C33095200282E (formation_id), INDEX IDX_DA4C3309A76ED395 (user_id), PRIMARY KEY(formation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT DEFAULT NULL, content LONGTEXT NOT NULL, salaire DOUBLE PRECISION NOT NULL, description LONGTEXT NOT NULL, date_poste DATE NOT NULL, INDEX IDX_5A8A6C8DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE projet (id BIGINT AUTO_INCREMENT NOT NULL, nom_projet VARCHAR(255) NOT NULL, equipe VARCHAR(255) NOT NULL, responsable VARCHAR(255) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE question (id BIGINT AUTO_INCREMENT NOT NULL, score INT NOT NULL, category VARCHAR(255) NOT NULL, difficultylevel VARCHAR(255) NOT NULL, option1 VARCHAR(255) NOT NULL, option2 VARCHAR(255) NOT NULL, option3 VARCHAR(255) NOT NULL, option4 VARCHAR(255) NOT NULL, question_title VARCHAR(255) NOT NULL, right_answer VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quiz (id BIGINT AUTO_INCREMENT NOT NULL, category VARCHAR(255) NOT NULL, difficultylevel VARCHAR(255) NOT NULL, minimum_success_percentage DOUBLE PRECISION NOT NULL, passer VARCHAR(1) NOT NULL, title VARCHAR(255) NOT NULL, quiz_time INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE quiz_questions (quiz_id BIGINT NOT NULL, questions_id BIGINT NOT NULL, INDEX IDX_8CBC2533853CD175 (quiz_id), INDEX IDX_8CBC2533BCB134CE (questions_id), PRIMARY KEY(quiz_id, questions_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT DEFAULT NULL, sujet VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, statut VARCHAR(50) NOT NULL, date_creation DATE NOT NULL, INDEX IDX_CE606404A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE response (id VARCHAR(255) NOT NULL, quiz_id BIGINT DEFAULT NULL, resultat_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_3E7B0BFB853CD175 (quiz_id), INDEX IDX_3E7B0BFBD233E95C (resultat_id), INDEX IDX_3E7B0BFBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE response_responses (id INT AUTO_INCREMENT NOT NULL, response_id VARCHAR(255) DEFAULT NULL, answer VARCHAR(255) NOT NULL, question VARCHAR(255) NOT NULL, INDEX IDX_1F5C84F3FBF32840 (response_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat (id BIGINT AUTO_INCREMENT NOT NULL, score INT NOT NULL, percentage DOUBLE PRECISION NOT NULL, resultat INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE session (id BIGINT AUTO_INCREMENT NOT NULL, formation_id BIGINT DEFAULT NULL, salle VARCHAR(255) NOT NULL, date DATE NOT NULL, link VARCHAR(255) NOT NULL, is_online TINYINT(1) NOT NULL, INDEX IDX_D044D5D45200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id BIGINT AUTO_INCREMENT NOT NULL, id_equipe BIGINT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, solde_conge INT NOT NULL, INDEX IDX_8D93D64927E0FF8 (id_equipe), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_equipe (equipe_id BIGINT NOT NULL, user_id BIGINT NOT NULL, INDEX IDX_411BA128A76ED395 (user_id), PRIMARY KEY(user_id, equipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_quiz (user_id BIGINT NOT NULL, quiz_id BIGINT NOT NULL, INDEX IDX_DE93B65BA76ED395 (user_id), INDEX IDX_DE93B65B853CD175 (quiz_id), PRIMARY KEY(user_id, quiz_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conge ADD CONSTRAINT FK_2ED89348CAAC9A59 FOREIGN KEY (conge_id) REFERENCES demande_conge (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conge ADD CONSTRAINT FK_2ED89348A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_conge ADD CONSTRAINT FK_D8061061A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_mobilite ADD CONSTRAINT FK_84083591A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C33095200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C3309A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC2533853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC2533BCB134CE FOREIGN KEY (questions_id) REFERENCES question (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBD233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response_responses ADD CONSTRAINT FK_1F5C84F3FBF32840 FOREIGN KEY (response_id) REFERENCES response (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D45200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64927E0FF8 FOREIGN KEY (id_equipe) REFERENCES equipe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_equipe ADD CONSTRAINT FK_411BA128A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_quiz ADD CONSTRAINT FK_DE93B65BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_quiz ADD CONSTRAINT FK_DE93B65B853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conge DROP FOREIGN KEY FK_2ED89348CAAC9A59
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conge DROP FOREIGN KEY FK_2ED89348A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_conge DROP FOREIGN KEY FK_D8061061A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande_mobilite DROP FOREIGN KEY FK_84083591A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA15AE80F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C33095200282E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C3309A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_questions DROP FOREIGN KEY FK_8CBC2533853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_questions DROP FOREIGN KEY FK_8CBC2533BCB134CE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBD233E95C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE response_responses DROP FOREIGN KEY FK_1F5C84F3FBF32840
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D45200282E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64927E0FF8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_equipe DROP FOREIGN KEY FK_411BA128A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_quiz DROP FOREIGN KEY FK_DE93B65BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_quiz DROP FOREIGN KEY FK_DE93B65B853CD175
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE chat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande_conge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande_mobilite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE department
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE equipe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formation_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE projet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quiz
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quiz_questions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE response
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE response_responses
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_equipe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_quiz
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
