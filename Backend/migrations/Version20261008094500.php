<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008094500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE decision ADD agenda_point_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48C1707BC6 FOREIGN KEY (agenda_point_id) REFERENCES agenda_point (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_84ACBE48C1707BC6 ON decision (agenda_point_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48C1707BC6');
        $this->addSql('DROP INDEX IDX_84ACBE48C1707BC6 ON decision');
        $this->addSql('ALTER TABLE decision DROP agenda_point_id');
    }
}
