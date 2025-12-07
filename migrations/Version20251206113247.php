<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206113247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicebooking ADD staff_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicebooking ADD CONSTRAINT FK_27F80F6BD4D57CD FOREIGN KEY (staff_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_27F80F6BD4D57CD ON servicebooking (staff_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicebooking DROP FOREIGN KEY FK_27F80F6BD4D57CD');
        $this->addSql('DROP INDEX IDX_27F80F6BD4D57CD ON servicebooking');
        $this->addSql('ALTER TABLE servicebooking DROP staff_id');
    }
}
