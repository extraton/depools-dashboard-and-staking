<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201218193821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO net VALUES (1, 'main.ton.dev'), (2, 'net.ton.dev')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('TRUNCATE net');
    }
}
