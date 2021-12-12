<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211211105815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, ticker_id INT NOT NULL, market_id INT NOT NULL, client_order_id INT NOT NULL, opened DATETIME DEFAULT NULL, last_trade DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, side VARCHAR(255) NOT NULL, price NUMERIC(20, 8) DEFAULT NULL, average NUMERIC(20, 8) DEFAULT NULL, amount NUMERIC(10, 4) NOT NULL, filled NUMERIC(10, 4) DEFAULT NULL, remaining NUMERIC(10, 4) DEFAULT NULL, cost NUMERIC(20, 8) DEFAULT NULL, fees LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_F5299398556B180E (ticker_id), INDEX IDX_F5299398622F3F37 (market_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398556B180E FOREIGN KEY (ticker_id) REFERENCES ticker (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398622F3F37 FOREIGN KEY (market_id) REFERENCES market (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `order`');
    }
}
