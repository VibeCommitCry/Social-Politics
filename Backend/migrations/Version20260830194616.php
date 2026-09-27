<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830194616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE decision (id CHAR(36) NOT NULL, agenda_item_id CHAR(36) NOT NULL, type VARCHAR(255) DEFAULT NULL, text LONGTEXT DEFAULT NULL, remark LONGTEXT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, sequence INT DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fraction (id CHAR(36) NOT NULL, abbreviation VARCHAR(255) DEFAULT NULL, name_nl VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, seat_count INT DEFAULT NULL, vote_count INT DEFAULT NULL, active_from DATETIME DEFAULT NULL, active_until DATETIME DEFAULT NULL, deleted TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE goverment_case (id CHAR(36) NOT NULL, number VARCHAR(20) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, title LONGTEXT DEFAULT NULL, subject LONGTEXT DEFAULT NULL, started_on DATE DEFAULT NULL, organization VARCHAR(30) DEFAULT NULL, current_status VARCHAR(100) DEFAULT NULL, cabinet_position VARCHAR(100) DEFAULT NULL, completed TINYINT NOT NULL, deleted TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE goverment_case_decision (goverment_case_id CHAR(36) NOT NULL, decision_id CHAR(36) NOT NULL, INDEX IDX_208B7DC6F0260DF7 (goverment_case_id), INDEX IDX_208B7DC6BDEE7539 (decision_id), PRIMARY KEY (goverment_case_id, decision_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vote (id CHAR(36) NOT NULL, type VARCHAR(255) DEFAULT NULL, fraction_size INT DEFAULT NULL, fraction_name VARCHAR(255) DEFAULT NULL, actor_name VARCHAR(255) DEFAULT NULL, mistake TINYINT DEFAULT NULL, person_id CHAR(36) DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, decision_id CHAR(36) NOT NULL, fraction_id CHAR(36) DEFAULT NULL, INDEX IDX_5A108564BDEE7539 (decision_id), INDEX IDX_5A1085645A615CA9 (fraction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6F0260DF7 FOREIGN KEY (goverment_case_id) REFERENCES goverment_case (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE goverment_case_decision ADD CONSTRAINT FK_208B7DC6BDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564BDEE7539 FOREIGN KEY (decision_id) REFERENCES decision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085645A615CA9 FOREIGN KEY (fraction_id) REFERENCES fraction (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6F0260DF7');
        $this->addSql('ALTER TABLE goverment_case_decision DROP FOREIGN KEY FK_208B7DC6BDEE7539');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564BDEE7539');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085645A615CA9');
        $this->addSql('DROP TABLE decision');
        $this->addSql('DROP TABLE fraction');
        $this->addSql('DROP TABLE goverment_case');
        $this->addSql('DROP TABLE goverment_case_decision');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vote');
    }
}
