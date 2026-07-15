<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260715143805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fraction ADD abbreviation VARCHAR(255) DEFAULT NULL, ADD name_nl VARCHAR(255) DEFAULT NULL, ADD name_en VARCHAR(255) DEFAULT NULL, ADD seat_count INT DEFAULT NULL, ADD vote_count INT DEFAULT NULL, ADD active_from DATETIME DEFAULT NULL, ADD active_until DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP afkorting, DROP naam_nl, DROP naam_en, DROP aantal_zetels, DROP aantal_stemmen, DROP datum_actief, DROP datum_inactief, DROP gewijzigd_op, CHANGE verwijderd deleted TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fraction ADD afkorting VARCHAR(255) DEFAULT NULL, ADD naam_nl VARCHAR(255) DEFAULT NULL, ADD naam_en VARCHAR(255) DEFAULT NULL, ADD aantal_zetels INT DEFAULT NULL, ADD aantal_stemmen INT DEFAULT NULL, ADD datum_actief DATETIME DEFAULT NULL, ADD datum_inactief DATETIME DEFAULT NULL, ADD gewijzigd_op DATETIME DEFAULT NULL, DROP abbreviation, DROP name_nl, DROP name_en, DROP seat_count, DROP vote_count, DROP active_from, DROP active_until, DROP updated_at, CHANGE deleted verwijderd TINYINT NOT NULL');
    }
}
