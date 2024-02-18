<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240209111829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE quotation_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, company_id INT NOT NULL, fullname VARCHAR(50) DEFAULT NULL, email VARCHAR(255) NOT NULL, number VARCHAR(50) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, tva VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_81398E09979B1AD6 ON customer (company_id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_prestation DROP CONSTRAINT fk_6d5aff0fb4ea4e60');
        $this->addSql('ALTER TABLE quotation_prestation DROP CONSTRAINT fk_6d5aff0f9e45c554');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT fk_474a8db98d57a4bb');
        $this->addSql('DROP TABLE quotation_prestation');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('ALTER TABLE company ADD siret VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE company ADD head_office VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE company ALTER tva TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE company ALTER tva DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_906517448d57a4bb');
        $this->addSql('DROP INDEX idx_906517448d57a4bb');
        $this->addSql('ALTER TABLE invoice ADD customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD closing_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice DROP user__id');
        $this->addSql('ALTER TABLE invoice DROP facture');
        $this->addSql('ALTER TABLE invoice ALTER status TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_906517449395C3F3 ON invoice (customer_id)');
        $this->addSql('ALTER TABLE prestation ADD description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE prestation ADD tva DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER company_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449395C3F3');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE quotation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE quotation_prestation (quotation_id INT NOT NULL, prestation_id INT NOT NULL, PRIMARY KEY(quotation_id, prestation_id))');
        $this->addSql('CREATE INDEX idx_6d5aff0f9e45c554 ON quotation_prestation (prestation_id)');
        $this->addSql('CREATE INDEX idx_6d5aff0fb4ea4e60 ON quotation_prestation (quotation_id)');
        $this->addSql('CREATE TABLE quotation (id INT NOT NULL, user__id INT NOT NULL, status BYTEA NOT NULL, devis JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_474a8db98d57a4bb ON quotation (user__id)');
        $this->addSql('COMMENT ON COLUMN quotation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN quotation.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE quotation_prestation ADD CONSTRAINT fk_6d5aff0fb4ea4e60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_prestation ADD CONSTRAINT fk_6d5aff0f9e45c554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT fk_474a8db98d57a4bb FOREIGN KEY (user__id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09979B1AD6');
        $this->addSql('DROP TABLE customer');
        $this->addSql('ALTER TABLE company DROP siret');
        $this->addSql('ALTER TABLE company DROP head_office');
        $this->addSql('ALTER TABLE company ALTER tva TYPE INT');
        $this->addSql('ALTER TABLE company ALTER tva SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER tva TYPE INT');
        $this->addSql('ALTER TABLE "user" ALTER company_id DROP NOT NULL');
        $this->addSql('DROP INDEX IDX_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice ADD user__id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD facture JSON NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP customer_id');
        $this->addSql('ALTER TABLE invoice DROP total');
        $this->addSql('ALTER TABLE invoice DROP type');
        $this->addSql('ALTER TABLE invoice DROP closing_date');
        $this->addSql('ALTER TABLE invoice ALTER status TYPE BYTEA');
        $this->addSql('ALTER TABLE invoice ALTER status TYPE BYTEA');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_906517448d57a4bb FOREIGN KEY (user__id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_906517448d57a4bb ON invoice (user__id)');
        $this->addSql('ALTER TABLE prestation DROP description');
        $this->addSql('ALTER TABLE prestation DROP tva');
    }
}
