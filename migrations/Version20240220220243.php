<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240220220243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ALTER siret TYPE VARCHAR(14)');
       // $this->addSql('ALTER TABLE contact ADD subject VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE prestation ALTER archive DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE company ALTER siret TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE prestation ALTER archive SET DEFAULT false');
        $this->addSql('ALTER TABLE contact DROP subject');
    }
}
