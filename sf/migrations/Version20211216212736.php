<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211216212736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticker ADD volume DOUBLE PRECISION DEFAULT NULL, ADD last DOUBLE PRECISION DEFAULT NULL, ADD average DOUBLE PRECISION DEFAULT NULL, ADD low DOUBLE PRECISION DEFAULT NULL, ADD high DOUBLE PRECISION DEFAULT NULL, ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticker DROP volume, DROP last, DROP average, DROP low, DROP high, DROP created, DROP updated');
    }
}
