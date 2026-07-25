<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717110550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fraction (id CHAR(36) NOT NULL, abbreviation VARCHAR(255) DEFAULT NULL, name_nl VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, seat_count INT DEFAULT NULL, vote_count INT DEFAULT NULL, active_from DATETIME DEFAULT NULL, active_until DATETIME DEFAULT NULL, deleted TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sync_cursor (category VARCHAR(64) NOT NULL, next_url LONGTEXT NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (category)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fraction');
        $this->addSql('DROP TABLE sync_cursor');
    }
}
