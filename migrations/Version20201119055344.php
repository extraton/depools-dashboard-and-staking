<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201119055344 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE depool (id BIGINT AUTO_INCREMENT NOT NULL, net_id BIGINT DEFAULT NULL, address VARCHAR(67) NOT NULL, info LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', stakes LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_ts DATETIME NOT NULL, INDEX IDX_FDA06248AFB31C5D (net_id), UNIQUE INDEX net_id__address (net_id, address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depool_round (id BIGINT AUTO_INCREMENT NOT NULL, depool_id BIGINT DEFAULT NULL, rid BIGINT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_ts DATETIME NOT NULL, INDEX IDX_BF58530299D5B378 (depool_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE net (id BIGINT AUTO_INCREMENT NOT NULL, server VARCHAR(67) NOT NULL, UNIQUE INDEX UNIQ_F2EA15FF5A6DD5F6 (server), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE depool ADD CONSTRAINT FK_FDA06248AFB31C5D FOREIGN KEY (net_id) REFERENCES net (id)');
        $this->addSql('ALTER TABLE depool_round ADD CONSTRAINT FK_BF58530299D5B378 FOREIGN KEY (depool_id) REFERENCES depool (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depool_round DROP FOREIGN KEY FK_BF58530299D5B378');
        $this->addSql('ALTER TABLE depool DROP FOREIGN KEY FK_FDA06248AFB31C5D');
        $this->addSql('DROP TABLE depool');
        $this->addSql('DROP TABLE depool_round');
        $this->addSql('DROP TABLE net');
    }
}
