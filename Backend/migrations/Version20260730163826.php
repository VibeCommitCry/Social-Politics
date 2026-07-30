<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260730163826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY `FK_208B7DC6BDEE7539`');
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY `FK_208B7DC6F0260DF7`');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6BDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6F0260DF7 FOREIGN KEY (goverment_case_id) REFERENCES goverment_case (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6F0260DF7');
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6BDEE7539');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT `FK_208B7DC6F0260DF7` FOREIGN KEY (goverment_case_id) REFERENCES goverment_case (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT `FK_208B7DC6BDEE7539` FOREIGN KEY (decision_id) REFERENCES decision (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
