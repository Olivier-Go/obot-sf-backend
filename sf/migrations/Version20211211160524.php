<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211211160524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE average average DOUBLE PRECISION NOT NULL, CHANGE amount amount DOUBLE PRECISION NOT NULL, CHANGE filled filled DOUBLE PRECISION NOT NULL, CHANGE remaining remaining DOUBLE PRECISION NOT NULL, CHANGE cost cost DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE price price NUMERIC(20, 8) DEFAULT NULL, CHANGE average average NUMERIC(20, 8) DEFAULT NULL, CHANGE amount amount NUMERIC(10, 4) NOT NULL, CHANGE filled filled NUMERIC(10, 4) DEFAULT NULL, CHANGE remaining remaining NUMERIC(10, 4) DEFAULT NULL, CHANGE cost cost NUMERIC(20, 8) DEFAULT NULL');
    }
}
