<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201209133355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE action_statuses ADD ttl INT NOT NULL default 3600');
        $this->addSql('ALTER TABLE incident_statuses ADD ttl INT NOT NULL default 3600');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE incident_statuses DROP ttl');
        $this->addSql('ALTER TABLE action_statuses DROP ttl');
    }
}
