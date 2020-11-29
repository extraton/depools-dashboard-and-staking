<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201127174323 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE depool_event (id BIGINT AUTO_INCREMENT NOT NULL, depool_id BIGINT DEFAULT NULL, eid VARCHAR(64) NOT NULL, name VARCHAR(64) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_ts DATETIME NOT NULL, INDEX IDX_4118B39199D5B378 (depool_id), UNIQUE INDEX depool_id__eid (depool_id, eid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE depool_event ADD CONSTRAINT FK_4118B39199D5B378 FOREIGN KEY (depool_id) REFERENCES depool (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE depool_event');
    }
}
