<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240105190052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE offer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prestation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quotation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE contact (id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(10) NOT NULL, society_size INT NOT NULL, society_name VARCHAR(255) NOT NULL, message TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE invoice (id INT NOT NULL, user__id INT DEFAULT NULL, status BYTEA NOT NULL, facture JSON NOT NULL, price INT NOT NULL , created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_906517448D57A4BB ON invoice (user__id)');
        $this->addSql('COMMENT ON COLUMN invoice.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE invoice_prestation (invoice_id INT NOT NULL, prestation_id INT NOT NULL, PRIMARY KEY(invoice_id, prestation_id))');
        $this->addSql('CREATE INDEX IDX_A814FF332989F1FD ON invoice_prestation (invoice_id)');
        $this->addSql('CREATE INDEX IDX_A814FF339E45C554 ON invoice_prestation (prestation_id)');
        $this->addSql('CREATE TABLE offer (id INT NOT NULL, name VARCHAR(45) NOT NULL, allowed_accountant INT NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prestation (id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(45) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_51C88FAD979B1AD6 ON prestation (company_id)');
        $this->addSql('CREATE TABLE quotation (id INT NOT NULL, user__id INT NOT NULL, status BYTEA NOT NULL, devis JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_474A8DB98D57A4BB ON quotation (user__id)');
        $this->addSql('COMMENT ON COLUMN quotation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN quotation.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE quotation_prestation (quotation_id INT NOT NULL, prestation_id INT NOT NULL, PRIMARY KEY(quotation_id, prestation_id))');
        $this->addSql('CREATE INDEX IDX_6D5AFF0FB4EA4E60 ON quotation_prestation (quotation_id)');
        $this->addSql('CREATE INDEX IDX_6D5AFF0F9E45C554 ON quotation_prestation (prestation_id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448D57A4BB FOREIGN KEY (user__id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT FK_A814FF332989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT FK_A814FF339E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB98D57A4BB FOREIGN KEY (user__id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_prestation ADD CONSTRAINT FK_6D5AFF0FB4EA4E60 FOREIGN KEY (quotation_id) REFERENCES quotation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quotation_prestation ADD CONSTRAINT FK_6D5AFF0F9E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company ADD offer_id INT NOT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4FBF094F53C674EE ON company (offer_id)');
        $this->addSql('ALTER INDEX idx_b2a93c5b9d86650f RENAME TO IDX_B2A93C5BA76ED395');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74');
        $this->addSql('ALTER TABLE "user" ADD company_id INT');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649979B1AD6 ON "user" (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094F53C674EE');
        $this->addSql('DROP SEQUENCE contact_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE offer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prestation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quotation_id_seq CASCADE');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517448D57A4BB');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT FK_A814FF332989F1FD');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT FK_A814FF339E45C554');
        $this->addSql('ALTER TABLE prestation DROP CONSTRAINT FK_51C88FAD979B1AD6');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT FK_474A8DB98D57A4BB');
        $this->addSql('ALTER TABLE quotation_prestation DROP CONSTRAINT FK_6D5AFF0FB4EA4E60');
        $this->addSql('ALTER TABLE quotation_prestation DROP CONSTRAINT FK_6D5AFF0F9E45C554');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_prestation');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('DROP TABLE quotation_prestation');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649979B1AD6');
        $this->addSql('DROP INDEX IDX_8D93D649979B1AD6');
        $this->addSql('ALTER TABLE "user" DROP company_id');
        $this->addSql('ALTER TABLE "user" ALTER lastname DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER created_at SET DEFAULT \'CURRENT_TIMESTAMP(0)\'');
        $this->addSql('ALTER TABLE "user" ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER updated_at SET DEFAULT \'CURRENT_TIMESTAMP(0)\'');
        $this->addSql('ALTER TABLE "user" ALTER updated_at DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user" (email)');
        $this->addSql('DROP INDEX IDX_4FBF094F53C674EE');
        $this->addSql('ALTER TABLE company DROP offer_id');
        $this->addSql('ALTER INDEX idx_b2a93c5ba76ed395 RENAME TO idx_b2a93c5b9d86650f');
    }
}
