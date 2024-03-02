<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240302193626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP tva');
        $this->addSql('ALTER TABLE company DROP license_validity');
        $this->addSql('ALTER TABLE company DROP id_license');
        $this->addSql('ALTER TABLE customer DROP tva');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE company ADD tva VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD license_validity DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD id_license INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD tva VARCHAR(50) DEFAULT NULL');
    }
}
