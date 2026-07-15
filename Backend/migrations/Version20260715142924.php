<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260715142924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fraction (id CHAR(36) NOT NULL, afkorting VARCHAR(255) DEFAULT NULL, naam_nl VARCHAR(255) DEFAULT NULL, naam_en VARCHAR(255) DEFAULT NULL, aantal_zetels INT DEFAULT NULL, aantal_stemmen INT DEFAULT NULL, datum_actief DATETIME DEFAULT NULL, datum_inactief DATETIME DEFAULT NULL, verwijderd TINYINT NOT NULL, gewijzigd_op DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sync_cursor (category VARCHAR(64) NOT NULL, next_url LONGTEXT NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (category)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('DROP TABLE user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE fraction');
        $this->addSql('DROP TABLE sync_cursor');
    }
}
