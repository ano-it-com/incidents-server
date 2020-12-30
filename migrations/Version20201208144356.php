<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201208144356 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "sso_user_token" (internal_token VARCHAR(50) NOT NULL, user_id UUID NOT NULL, type VARCHAR(100) NOT NULL, expires TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sso_token VARCHAR(5000) NOT NULL, PRIMARY KEY(internal_token))');
        $this->addSql('CREATE INDEX IDX_AF74179DA76ED395 ON "sso_user_token" (user_id)');
        $this->addSql('CREATE TABLE "sso_users" (id UUID NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, banned_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_ssoadmin BOOLEAN NOT NULL, from_ldap BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F7FA917F85E0677 ON "sso_users" (username)');
        $this->addSql('ALTER TABLE "sso_user_token" ADD CONSTRAINT FK_AF74179DA76ED395 FOREIGN KEY (user_id) REFERENCES "sso_users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "sso_user_token" DROP CONSTRAINT FK_AF74179DA76ED395');
        $this->addSql('DROP TABLE "sso_user_token"');
        $this->addSql('DROP TABLE "sso_users"');
    }
}
