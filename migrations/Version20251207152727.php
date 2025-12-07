<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207152727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walkin_orders ADD product_id INT NOT NULL, ADD payment_method VARCHAR(50) NOT NULL, ADD payment_status VARCHAR(20) NOT NULL, ADD quantity INT NOT NULL, ADD warranty_text LONGTEXT DEFAULT NULL, ADD purchase_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP paymentmethod, CHANGE contact contact VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE walkin_orders ADD CONSTRAINT FK_BC1CA7244584665A FOREIGN KEY (product_id) REFERENCES pcproducts (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_BC1CA7244584665A ON walkin_orders (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walkin_orders DROP FOREIGN KEY FK_BC1CA7244584665A');
        $this->addSql('DROP INDEX IDX_BC1CA7244584665A ON walkin_orders');
        $this->addSql('ALTER TABLE walkin_orders ADD paymentmethod VARCHAR(255) NOT NULL, DROP product_id, DROP payment_method, DROP payment_status, DROP quantity, DROP warranty_text, DROP purchase_date, CHANGE contact contact VARCHAR(255) NOT NULL');
    }
}
