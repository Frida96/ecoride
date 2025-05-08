<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424172653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet ADD depart VARCHAR(255) NOT NULL, ADD arrivee VARCHAR(255) NOT NULL, ADD heure_depart TIME DEFAULT NULL, ADD heure_arrivee TIME DEFAULT NULL, DROP départ, DROP arrivée, DROP heure_départ, DROP heure_arrivée
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet ADD départ VARCHAR(255) NOT NULL, ADD arrivée VARCHAR(255) NOT NULL, ADD heure_départ TIME DEFAULT NULL, ADD heure_arrivée TIME DEFAULT NULL, DROP depart, DROP arrivee, DROP heure_depart, DROP heure_arrivee
        SQL);
    }
}
