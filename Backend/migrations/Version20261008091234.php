<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008091234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id CHAR(36) NOT NULL, type VARCHAR(255) DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, subject LONGTEXT DEFAULT NULL, date_type VARCHAR(255) DEFAULT NULL, date DATETIME DEFAULT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, private TINYINT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, parliamentary_year VARCHAR(255) DEFAULT NULL, room VARCHAR(255) DEFAULT NULL, remark LONGTEXT DEFAULT NULL, lead_committee_name VARCHAR(255) DEFAULT NULL, lead_committee_abbreviation VARCHAR(255) DEFAULT NULL, lead_committee_short_name VARCHAR(255) DEFAULT NULL, lead_committee_id CHAR(36) DEFAULT NULL, request_date DATETIME DEFAULT NULL, first_extension_request_date DATETIME DEFAULT NULL, first_extension_notice_date DATETIME DEFAULT NULL, second_extension_request_date DATETIME DEFAULT NULL, second_extension_notice_date DATETIME DEFAULT NULL, expiry_date DATETIME DEFAULT NULL, modified_on DATETIME DEFAULT NULL, api_modified_on DATETIME DEFAULT NULL, deleted TINYINT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity');
    }
}
