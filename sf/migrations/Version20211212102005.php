<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211212102005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE average average DOUBLE PRECISION DEFAULT NULL, CHANGE filled filled DOUBLE PRECISION DEFAULT NULL, CHANGE remaining remaining DOUBLE PRECISION DEFAULT NULL, CHANGE cost cost DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE average average DOUBLE PRECISION NOT NULL, CHANGE filled filled DOUBLE PRECISION NOT NULL, CHANGE remaining remaining DOUBLE PRECISION NOT NULL, CHANGE cost cost DOUBLE PRECISION NOT NULL');
    }
}
