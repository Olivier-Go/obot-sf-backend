<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205143853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE market (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, api_key VARCHAR(255) DEFAULT NULL, api_secret VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticker ADD market_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ticker ADD CONSTRAINT FK_7EC30896622F3F37 FOREIGN KEY (market_id) REFERENCES market (id)');
        $this->addSql('CREATE INDEX IDX_7EC30896622F3F37 ON ticker (market_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticker DROP FOREIGN KEY FK_7EC30896622F3F37');
        $this->addSql('DROP TABLE market');
        $this->addSql('DROP INDEX IDX_7EC30896622F3F37 ON ticker');
        $this->addSql('ALTER TABLE ticker DROP market_id');
    }
}
