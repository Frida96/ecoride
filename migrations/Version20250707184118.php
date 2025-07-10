<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707184118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id SERIAL NOT NULL, passager_id INT NOT NULL, chauffeur_id INT NOT NULL, note INT NOT NULL, commentaire TEXT DEFAULT NULL, valide BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F91ABF071A51189 ON avis (passager_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF085C0B3BE ON avis (chauffeur_id)');
        $this->addSql('CREATE TABLE participation (id SERIAL NOT NULL, passager_id INT NOT NULL, trajet_id INT NOT NULL, statut VARCHAR(20) DEFAULT \'confirmee\' NOT NULL, double_validation BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB55E24F71A51189 ON participation (passager_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24FD12A823 ON participation (trajet_id)');
        $this->addSql('CREATE TABLE trajet (id SERIAL NOT NULL, chauffeur_id INT NOT NULL, vehicule_id INT NOT NULL, lieu_depart VARCHAR(100) NOT NULL, lieu_arrivee VARCHAR(100) NOT NULL, date_depart TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_arrivee TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, nb_places INT NOT NULL, prix INT NOT NULL, statut VARCHAR(20) DEFAULT \'en_attente\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2B5BA98C85C0B3BE ON trajet (chauffeur_id)');
        $this->addSql('CREATE INDEX IDX_2B5BA98C4A4A3511 ON trajet (vehicule_id)');
        $this->addSql('CREATE TABLE utilisateur (id SERIAL NOT NULL, pseudo VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, verifie BOOLEAN NOT NULL, preferences VARCHAR(255) DEFAULT NULL, credit INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE vehicule (id SERIAL NOT NULL, utilisateur_id INT NOT NULL, immatriculation VARCHAR(20) NOT NULL, marque VARCHAR(50) NOT NULL, modele VARCHAR(50) NOT NULL, couleur VARCHAR(30) NOT NULL, energie VARCHAR(20) NOT NULL, date_premier_immatriculation DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_292FFF1DFB88E14F ON vehicule (utilisateur_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF071A51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF085C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71A51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FD12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF071A51189');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF085C0B3BE');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24F71A51189');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24FD12A823');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT FK_2B5BA98C85C0B3BE');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT FK_2B5BA98C4A4A3511');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT FK_292FFF1DFB88E14F');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE trajet');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
