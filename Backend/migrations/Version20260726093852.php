<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726093852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vote (id CHAR(36) NOT NULL, decision_id CHAR(36) NOT NULL, type VARCHAR(255) DEFAULT NULL, fraction_size INT DEFAULT NULL, actor_name VARCHAR(255) DEFAULT NULL, fraction_name VARCHAR(255) DEFAULT NULL, mistake TINYINT DEFAULT NULL, person_id CHAR(36) DEFAULT NULL, fraction_id CHAR(36) DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('DROP TABLE sync_cursor');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sync_cursor (category VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, next_url LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, updated_at DATETIME NOT NULL, PRIMARY KEY (category)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE vote');
    }
}
