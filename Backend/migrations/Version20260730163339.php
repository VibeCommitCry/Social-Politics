<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260730163339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE goverment_case_decision (goverment_case_id CHAR(36) NOT NULL, decision_id CHAR(36) NOT NULL, INDEX IDX_208B7DC6F0260DF7 (goverment_case_id), INDEX IDX_208B7DC6BDEE7539 (decision_id), PRIMARY KEY (goverment_case_id, decision_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6F0260DF7 FOREIGN KEY (goverment_case_id) REFERENCES goverment_case (id)');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6BDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6F0260DF7');
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6BDEE7539');
        $this->addSql('DROP TABLE goverment_case_decision');
    }
}
