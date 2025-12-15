<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209033639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walkin_orders DROP FOREIGN KEY FK_BC1CA7244584665A');
        $this->addSql('ALTER TABLE walkin_orders ADD CONSTRAINT FK_BC1CA7244584665A FOREIGN KEY (product_id) REFERENCES pcproducts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walkin_orders DROP FOREIGN KEY FK_BC1CA7244584665A');
        $this->addSql('ALTER TABLE walkin_orders ADD CONSTRAINT FK_BC1CA7244584665A FOREIGN KEY (product_id) REFERENCES pcproducts (id) ON UPDATE NO ACTION');
    }
}
