<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201211090203 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE action_statuses DROP ttl');
        $this->addSql('ALTER TABLE incident_statuses DROP ttl');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE action_statuses ADD ttl INT DEFAULT 3600 NOT NULL');
        $this->addSql('ALTER TABLE incident_statuses ADD ttl INT DEFAULT 3600 NOT NULL');
    }
}
