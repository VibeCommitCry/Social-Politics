<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260727201626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE decision (id CHAR(36) NOT NULL, agenda_item_id CHAR(36) NOT NULL, vote_method VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, text LONGTEXT DEFAULT NULL, remark LONGTEXT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, sequence INT DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE decision');
    }
}
