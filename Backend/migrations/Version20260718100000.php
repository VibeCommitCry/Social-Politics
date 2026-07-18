<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE goverment_case (id CHAR(36) NOT NULL, number VARCHAR(20) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, title LONGTEXT DEFAULT NULL, subject LONGTEXT DEFAULT NULL, started_on DATE DEFAULT NULL, organization VARCHAR(30) DEFAULT NULL, current_status VARCHAR(100) DEFAULT NULL, cabinet_position VARCHAR(100) DEFAULT NULL, completed TINYINT NOT NULL, deleted TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE goverment_case');
    }
}
