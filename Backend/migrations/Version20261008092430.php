<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008092430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agenda_point (id CHAR(36) NOT NULL, number VARCHAR(255) DEFAULT NULL, subject LONGTEXT DEFAULT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, sequence INT DEFAULT NULL, heading VARCHAR(255) DEFAULT NULL, remark LONGTEXT DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, activity_id CHAR(36) DEFAULT NULL, INDEX IDX_E79DA7BA81C06096 (activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE agenda_point ADD CONSTRAINT FK_E79DA7BA81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda_point DROP FOREIGN KEY FK_E79DA7BA81C06096');
        $this->addSql('DROP TABLE agenda_point');
    }
}
