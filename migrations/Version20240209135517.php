<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240209135517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE invoice_prestation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT FK_A814FF332989F1FD');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT FK_A814FF339E45C554');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT invoice_prestation_pkey');
        $this->addSql('ALTER TABLE invoice_prestation ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ADD quantity INT NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ALTER invoice_id DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ALTER prestation_id DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT FK_A814FF332989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT FK_A814FF339E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE invoice_prestation_id_seq CASCADE');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT fk_a814ff339e45c554');
        $this->addSql('ALTER TABLE invoice_prestation DROP CONSTRAINT fk_a814ff332989f1fd');
        $this->addSql('DROP INDEX invoice_prestation_pkey');
        $this->addSql('ALTER TABLE invoice_prestation DROP id');
        $this->addSql('ALTER TABLE invoice_prestation DROP quantity');
        $this->addSql('ALTER TABLE invoice_prestation ALTER prestation_id SET NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ALTER invoice_id SET NOT NULL');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT fk_a814ff339e45c554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD CONSTRAINT fk_a814ff332989f1fd FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_prestation ADD PRIMARY KEY (invoice_id, prestation_id)');
    }
}
