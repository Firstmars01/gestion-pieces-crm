<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617124511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realisation DROP CONSTRAINT fk_eaa5610ed2fd85f1');
        $this->addSql('ALTER TABLE realisation ADD gamme_libelle_archive VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation ADD piece_reference_archive VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation ALTER gamme_id DROP NOT NULL');
        $this->addSql('ALTER TABLE realisation ADD CONSTRAINT FK_EAA5610ED2FD85F1 FOREIGN KEY (gamme_id) REFERENCES gamme (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE realisation_poste DROP CONSTRAINT fk_b62960fb27b99e9');
        $this->addSql('DROP INDEX idx_b62960fb27b99e9');
        $this->addSql('ALTER TABLE realisation_poste ADD operation_libelle_archive VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation_poste ADD temps_prevu INT DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation_poste ADD operation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE realisation_poste ALTER temps DROP NOT NULL');
        $this->addSql('ALTER TABLE realisation_poste ALTER poste_machine_id DROP NOT NULL');
        $this->addSql('ALTER TABLE realisation_poste RENAME COLUMN gamme_operation_id TO ordre');
        $this->addSql('ALTER TABLE realisation_poste ADD CONSTRAINT FK_B62960FB44AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_B62960FB44AC3583 ON realisation_poste (operation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realisation DROP CONSTRAINT FK_EAA5610ED2FD85F1');
        $this->addSql('ALTER TABLE realisation DROP gamme_libelle_archive');
        $this->addSql('ALTER TABLE realisation DROP piece_reference_archive');
        $this->addSql('ALTER TABLE realisation ALTER gamme_id SET NOT NULL');
        $this->addSql('ALTER TABLE realisation ADD CONSTRAINT fk_eaa5610ed2fd85f1 FOREIGN KEY (gamme_id) REFERENCES gamme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE realisation_poste DROP CONSTRAINT FK_B62960FB44AC3583');
        $this->addSql('DROP INDEX IDX_B62960FB44AC3583');
        $this->addSql('ALTER TABLE realisation_poste DROP operation_libelle_archive');
        $this->addSql('ALTER TABLE realisation_poste DROP temps_prevu');
        $this->addSql('ALTER TABLE realisation_poste DROP operation_id');
        $this->addSql('ALTER TABLE realisation_poste ALTER temps SET NOT NULL');
        $this->addSql('ALTER TABLE realisation_poste ALTER poste_machine_id SET NOT NULL');
        $this->addSql('ALTER TABLE realisation_poste RENAME COLUMN ordre TO gamme_operation_id');
        $this->addSql('ALTER TABLE realisation_poste ADD CONSTRAINT fk_b62960fb27b99e9 FOREIGN KEY (gamme_operation_id) REFERENCES gamme_operation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_b62960fb27b99e9 ON realisation_poste (gamme_operation_id)');
    }
}
