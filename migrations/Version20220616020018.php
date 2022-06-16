<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616020018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2151017D5');
        $this->addSql('DROP INDEX IDX_E7DB5DE2151017D5 ON resultat');
        $this->addSql('ALTER TABLE resultat DROP retenus_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67058AB46C6E55B5 ON retenus (nom)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resultat ADD retenus_id INT NOT NULL');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2151017D5 FOREIGN KEY (retenus_id) REFERENCES retenus (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E7DB5DE2151017D5 ON resultat (retenus_id)');
        $this->addSql('DROP INDEX UNIQ_67058AB46C6E55B5 ON retenus');
    }
}
