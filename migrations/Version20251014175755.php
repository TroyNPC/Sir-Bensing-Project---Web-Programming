<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251014175755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocks DROP FOREIGN KEY FK_56F79805EA583AF1');
        $this->addSql('ALTER TABLE stocks ADD CONSTRAINT FK_56F79805EA583AF1 FOREIGN KEY (productname_id) REFERENCES pcproducts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocks DROP FOREIGN KEY FK_56F79805EA583AF1');
        $this->addSql('ALTER TABLE stocks ADD CONSTRAINT FK_56F79805EA583AF1 FOREIGN KEY (productname_id) REFERENCES pcproducts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
