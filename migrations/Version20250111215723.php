<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250111215723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sitter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, sitter_id INT DEFAULT NULL, reservation_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_5058659761F367C9 (sitter_id), INDEX IDX_50586597B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_5058659761F367C9 FOREIGN KEY (sitter_id) REFERENCES sitter (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597B83297E7 FOREIGN KEY (reservation_id) REFERENCES tasks (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_5058659761F367C9');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597B83297E7');
        $this->addSql('DROP TABLE sitter');
        $this->addSql('DROP TABLE tasks');
    }
}
