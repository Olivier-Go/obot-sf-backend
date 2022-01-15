<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220109115044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398556B180E');
        $this->addSql('DROP INDEX IDX_F5299398556B180E ON `order`');
        $this->addSql('ALTER TABLE `order` ADD ticker VARCHAR(255) NOT NULL, DROP ticker_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD ticker_id INT NOT NULL, DROP ticker');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398556B180E FOREIGN KEY (ticker_id) REFERENCES ticker (id)');
        $this->addSql('CREATE INDEX IDX_F5299398556B180E ON `order` (ticker_id)');
    }
}
