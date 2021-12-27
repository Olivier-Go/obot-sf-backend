<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211227204014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opportunity DROP FOREIGN KEY FK_8389C3D7556B180E');
        $this->addSql('DROP INDEX IDX_8389C3D7556B180E ON opportunity');
        $this->addSql('ALTER TABLE opportunity ADD ticker VARCHAR(255) NOT NULL, DROP ticker_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opportunity ADD ticker_id INT NOT NULL, DROP ticker');
        $this->addSql('ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D7556B180E FOREIGN KEY (ticker_id) REFERENCES ticker (id)');
        $this->addSql('CREATE INDEX IDX_8389C3D7556B180E ON opportunity (ticker_id)');
    }
}
