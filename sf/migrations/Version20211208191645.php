<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211208191645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE opportunity (id INT AUTO_INCREMENT NOT NULL, ticker_id INT NOT NULL, buy_market_id INT NOT NULL, sell_market_id INT NOT NULL, buy_price NUMERIC(10, 4) NOT NULL, sell_price NUMERIC(10, 4) NOT NULL, size NUMERIC(10, 4) NOT NULL, price_diff NUMERIC(10, 4) NOT NULL, received DATETIME NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_8389C3D7556B180E (ticker_id), INDEX IDX_8389C3D794644559 (buy_market_id), INDEX IDX_8389C3D7EFC6553E (sell_market_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D7556B180E FOREIGN KEY (ticker_id) REFERENCES ticker (id)');
        $this->addSql('ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D794644559 FOREIGN KEY (buy_market_id) REFERENCES market (id)');
        $this->addSql('ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D7EFC6553E FOREIGN KEY (sell_market_id) REFERENCES market (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE opportunity');
    }
}
