<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625090729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_devis (commande_id INT NOT NULL, devis_id INT NOT NULL, PRIMARY KEY (commande_id, devis_id))');
        $this->addSql('CREATE INDEX IDX_B24F1AE882EA2E54 ON commande_devis (commande_id)');
        $this->addSql('CREATE INDEX IDX_B24F1AE841DEFADA ON commande_devis (devis_id)');
        $this->addSql('ALTER TABLE commande_devis ADD CONSTRAINT FK_B24F1AE882EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_devis ADD CONSTRAINT FK_B24F1AE841DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande DROP CONSTRAINT fk_6eeaa67d41defada');
        $this->addSql('DROP INDEX idx_6eeaa67d41defada');
        $this->addSql('ALTER TABLE commande DROP devis_id');
        $this->addSql('ALTER TABLE devis ADD nom VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_devis DROP CONSTRAINT FK_B24F1AE882EA2E54');
        $this->addSql('ALTER TABLE commande_devis DROP CONSTRAINT FK_B24F1AE841DEFADA');
        $this->addSql('DROP TABLE commande_devis');
        $this->addSql('ALTER TABLE commande ADD devis_id INT NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT fk_6eeaa67d41defada FOREIGN KEY (devis_id) REFERENCES devis (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6eeaa67d41defada ON commande (devis_id)');
        $this->addSql('ALTER TABLE devis DROP nom');
    }
}
