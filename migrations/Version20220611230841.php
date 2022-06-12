<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220611230841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2FCD695F0');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2151017D5');
        $this->addSql('DROP INDEX UNIQ_E7DB5DE2FCD695F0 ON resultat');
        $this->addSql('ALTER TABLE resultat DROP represent_id');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2151017D5 FOREIGN KEY (retenus_id) REFERENCES retenus (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2151017D5');
        $this->addSql('ALTER TABLE resultat ADD represent_id INT NOT NULL');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2FCD695F0 FOREIGN KEY (represent_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2151017D5 FOREIGN KEY (retenus_id) REFERENCES resultat (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E7DB5DE2FCD695F0 ON resultat (represent_id)');
    }
}
