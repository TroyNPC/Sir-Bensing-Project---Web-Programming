<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209032407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pcproducts DROP FOREIGN KEY FK_A6B5D5BCB03A8386');
        $this->addSql('ALTER TABLE pcproducts ADD CONSTRAINT FK_A6B5D5BCB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE servicebooking DROP FOREIGN KEY FK_27F80F6BD4D57CD');
        $this->addSql('ALTER TABLE servicebooking ADD CONSTRAINT FK_27F80F6BD4D57CD FOREIGN KEY (staff_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE walkin_orders DROP FOREIGN KEY FK_BC1CA724B03A8386');
        $this->addSql('ALTER TABLE walkin_orders ADD CONSTRAINT FK_BC1CA724B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicebooking DROP FOREIGN KEY FK_27F80F6BD4D57CD');
        $this->addSql('ALTER TABLE servicebooking ADD CONSTRAINT FK_27F80F6BD4D57CD FOREIGN KEY (staff_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE walkin_orders DROP FOREIGN KEY FK_BC1CA724B03A8386');
        $this->addSql('ALTER TABLE walkin_orders ADD CONSTRAINT FK_BC1CA724B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pcproducts DROP FOREIGN KEY FK_A6B5D5BCB03A8386');
        $this->addSql('ALTER TABLE pcproducts ADD CONSTRAINT FK_A6B5D5BCB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
