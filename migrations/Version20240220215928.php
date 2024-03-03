<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220215928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       // $this->addSql('ALTER TABLE company DROP CONSTRAINT fk_4fbf094f53c674ee');
        //$this->addSql('DROP SEQUENCE offer_id_seq CASCADE');
        //$this->addSql('DROP TABLE offer');
        //his->addSql('DROP INDEX idx_4fbf094f53c674ee');
       // $this->addSql('ALTER TABLE company DROP offer_id');
        $this->addSql('ALTER TABLE company ALTER logo DROP NOT NULL');
        $this->addSql('ALTER TABLE company ALTER license_validity DROP NOT NULL');
        $this->addSql('ALTER TABLE company ALTER id_license DROP NOT NULL');
        $this->addSql('ALTER TABLE company ALTER siret TYPE VARCHAR(14)');
        $this->addSql('ALTER TABLE contact ADD subject VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE prestation ALTER archive DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER company_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER lastname DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE offer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE offer (id INT NOT NULL, name VARCHAR(45) NOT NULL, allowed_accountant INT NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE "user" ALTER company_id SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER lastname SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname SET NOT NULL');
        $this->addSql('ALTER TABLE prestation ALTER archive SET DEFAULT false');
        $this->addSql('ALTER TABLE contact DROP subject');
        $this->addSql('ALTER TABLE company ADD offer_id INT NOT NULL');
        $this->addSql('ALTER TABLE company ALTER logo SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER license_validity SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER id_license SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER siret TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT fk_4fbf094f53c674ee FOREIGN KEY (offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_4fbf094f53c674ee ON company (offer_id)');
    }
}
