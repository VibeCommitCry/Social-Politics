<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260927171443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY `FK_5A1085645A615CA9`');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085645A615CA9 FOREIGN KEY (fraction_id) REFERENCES fraction (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085645A615CA9');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT `FK_5A1085645A615CA9` FOREIGN KEY (fraction_id) REFERENCES fraction (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
