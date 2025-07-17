<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710174950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT fk_8f91abf071a51189');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT fk_8f91abf085c0b3be');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT fk_2b5ba98c85c0b3be');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT fk_ab55e24f71a51189');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT fk_292fff1dfb88e14f');
        $this->addSql('DROP SEQUENCE utilisateur_id_seq CASCADE');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF071A51189');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF085C0B3BE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF071A51189 FOREIGN KEY (passager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF085C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24F71A51189');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71A51189 FOREIGN KEY (passager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT FK_2B5BA98C85C0B3BE');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT FK_292FFF1DFB88E14F');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF071A51189');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF085C0B3BE');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT FK_AB55E24F71A51189');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT FK_2B5BA98C85C0B3BE');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT FK_292FFF1DFB88E14F');
        $this->addSql('CREATE SEQUENCE utilisateur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE utilisateur (id SERIAL NOT NULL, pseudo VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, verifie BOOLEAN NOT NULL, preferences VARCHAR(255) DEFAULT NULL, credit INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT fk_8f91abf071a51189');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT fk_8f91abf085c0b3be');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT fk_8f91abf071a51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT fk_8f91abf085c0b3be FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT fk_2b5ba98c85c0b3be');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT fk_2b5ba98c85c0b3be FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participation DROP CONSTRAINT fk_ab55e24f71a51189');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT fk_ab55e24f71a51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT fk_292fff1dfb88e14f');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT fk_292fff1dfb88e14f FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
