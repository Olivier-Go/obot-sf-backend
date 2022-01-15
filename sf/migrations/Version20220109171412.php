<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220109171412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opportunity ADD sell_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D76CF89127 FOREIGN KEY (sell_order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8389C3D76CF89127 ON opportunity (sell_order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opportunity DROP FOREIGN KEY FK_8389C3D76CF89127');
        $this->addSql('DROP INDEX UNIQ_8389C3D76CF89127 ON opportunity');
        $this->addSql('ALTER TABLE opportunity DROP sell_order_id');
    }
}
