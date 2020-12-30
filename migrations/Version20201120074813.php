<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201120074813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE action_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE action_task_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE action_task_types_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE action_tasks_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE action_types_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE actions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE actions_templates_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE categories_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE comments_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE files_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE group_permissions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE incident_statuses_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE incident_types_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE incidents_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE locations_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE permissions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "users_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE action_statuses (id INT NOT NULL, created_by_id INT NOT NULL, action_id INT NOT NULL, responsible_group_id INT NOT NULL, responsible_user_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C276E77B03A8386 ON action_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_9C276E779D32F035 ON action_statuses (action_id)');
        $this->addSql('CREATE INDEX IDX_9C276E7780FF6630 ON action_statuses (responsible_group_id)');
        $this->addSql('CREATE INDEX IDX_9C276E77BDAD1998 ON action_statuses (responsible_user_id)');
        $this->addSql('CREATE TABLE action_task_statuses (id INT NOT NULL, created_by_id INT NOT NULL, action_task_id INT NOT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30C2A641B03A8386 ON action_task_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_30C2A64157148967 ON action_task_statuses (action_task_id)');
        $this->addSql('CREATE TABLE action_task_types (id INT NOT NULL, title VARCHAR(255) NOT NULL, handler VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE action_tasks (id INT NOT NULL, action_id INT NOT NULL, type_id INT NOT NULL, status_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, input_data JSONB DEFAULT NULL, report_data JSONB DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B796A92B9D32F035 ON action_tasks (action_id)');
        $this->addSql('CREATE INDEX IDX_B796A92BC54C8C93 ON action_tasks (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B796A92B6BF700BD ON action_tasks (status_id)');
        $this->addSql('CREATE INDEX IDX_B796A92BB03A8386 ON action_tasks (created_by_id)');
        $this->addSql('CREATE INDEX IDX_B796A92B896DBBDE ON action_tasks (updated_by_id)');
        $this->addSql('CREATE TABLE action_types (id INT NOT NULL, title TEXT NOT NULL, sort INT NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE action_type_action_task_types (action_type_id INT NOT NULL, action_task_type_id INT NOT NULL, PRIMARY KEY(action_type_id, action_task_type_id))');
        $this->addSql('CREATE INDEX IDX_81AC7D011FEE0472 ON action_type_action_task_types (action_type_id)');
        $this->addSql('CREATE INDEX IDX_81AC7D0146DF7259 ON action_type_action_task_types (action_task_type_id)');
        $this->addSql('CREATE TABLE actions (id INT NOT NULL, type_id INT NOT NULL, status_id INT DEFAULT NULL, incident_id INT NOT NULL, responsible_group_id INT NOT NULL, responsible_user_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, template_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_548F1EFC54C8C93 ON actions (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_548F1EF6BF700BD ON actions (status_id)');
        $this->addSql('CREATE INDEX IDX_548F1EF59E53FB9 ON actions (incident_id)');
        $this->addSql('CREATE INDEX IDX_548F1EF80FF6630 ON actions (responsible_group_id)');
        $this->addSql('CREATE INDEX IDX_548F1EFBDAD1998 ON actions (responsible_user_id)');
        $this->addSql('CREATE INDEX IDX_548F1EFB03A8386 ON actions (created_by_id)');
        $this->addSql('CREATE INDEX IDX_548F1EF896DBBDE ON actions (updated_by_id)');
        $this->addSql('CREATE TABLE actions_templates (id INT NOT NULL, incident_type_id INT NOT NULL, actions_mapping JSONB NOT NULL, deleted BOOLEAN NOT NULL, sort INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CD90AB496286964E ON actions_templates (incident_type_id)');
        $this->addSql('CREATE TABLE categories (id INT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3AF34668727ACA70 ON categories (parent_id)');
        $this->addSql('CREATE TABLE comments (id INT NOT NULL, incident_id INT NOT NULL, action_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, target_group_id INT NOT NULL, text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F9E962A59E53FB9 ON comments (incident_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A9D32F035 ON comments (action_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AB03A8386 ON comments (created_by_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A896DBBDE ON comments (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A24FF092E ON comments (target_group_id)');
        $this->addSql('CREATE TABLE files (id INT NOT NULL, created_by_id INT NOT NULL, owner_code VARCHAR(255) NOT NULL, owner_id INT NOT NULL, path TEXT NOT NULL, original_name TEXT NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6354059B03A8386 ON files (created_by_id)');
        $this->addSql('CREATE INDEX fileable_idx ON files (owner_code, owner_id, deleted)');
        $this->addSql('CREATE TABLE group_permissions (id INT NOT NULL, group_id INT NOT NULL, permission_id INT NOT NULL, restriction JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_855D3AEFE54D947 ON group_permissions (group_id)');
        $this->addSql('CREATE INDEX IDX_855D3AEFED90CCA ON group_permissions (permission_id)');
        $this->addSql('CREATE UNIQUE INDEX group_permission_unique ON group_permissions (group_id, permission_id)');
        $this->addSql('CREATE TABLE groups (id INT NOT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, public BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE incident_statuses (id INT NOT NULL, created_by_id INT NOT NULL, incident_id INT NOT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B3357A62B03A8386 ON incident_statuses (created_by_id)');
        $this->addSql('CREATE INDEX IDX_B3357A6259E53FB9 ON incident_statuses (incident_id)');
        $this->addSql('CREATE TABLE incident_types (id INT NOT NULL, handler VARCHAR(255) NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE incident_type_action_types (incident_type_id INT NOT NULL, action_type_id INT NOT NULL, PRIMARY KEY(incident_type_id, action_type_id))');
        $this->addSql('CREATE INDEX IDX_C3D685806286964E ON incident_type_action_types (incident_type_id)');
        $this->addSql('CREATE INDEX IDX_C3D685801FEE0472 ON incident_type_action_types (action_type_id)');
        $this->addSql('CREATE TABLE incidents (id INT NOT NULL, type_id INT NOT NULL, status_id INT DEFAULT NULL, repeated_incident_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, info JSONB DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E65135D0C54C8C93 ON incidents (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E65135D06BF700BD ON incidents (status_id)');
        $this->addSql('CREATE INDEX IDX_E65135D0B04C3708 ON incidents (repeated_incident_id)');
        $this->addSql('CREATE INDEX IDX_E65135D0B03A8386 ON incidents (created_by_id)');
        $this->addSql('CREATE INDEX IDX_E65135D0896DBBDE ON incidents (updated_by_id)');
        $this->addSql('CREATE TABLE incident_responsible_groups (incident_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(incident_id, group_id))');
        $this->addSql('CREATE INDEX IDX_90559ABA59E53FB9 ON incident_responsible_groups (incident_id)');
        $this->addSql('CREATE INDEX IDX_90559ABAFE54D947 ON incident_responsible_groups (group_id)');
        $this->addSql('CREATE TABLE locations (id INT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, level INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_17E64ABA727ACA70 ON locations (parent_id)');
        $this->addSql('CREATE TABLE permissions (id INT NOT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, restriction_type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "users" (id INT NOT NULL, login VARCHAR(180) NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9AA08CB10 ON "users" (login)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql('CREATE TABLE users_groups (user_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(user_id, group_id))');
        $this->addSql('CREATE INDEX IDX_FF8AB7E0A76ED395 ON users_groups (user_id)');
        $this->addSql('CREATE INDEX IDX_FF8AB7E0FE54D947 ON users_groups (group_id)');
        $this->addSql('ALTER TABLE action_statuses ADD CONSTRAINT FK_9C276E77B03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_statuses ADD CONSTRAINT FK_9C276E779D32F035 FOREIGN KEY (action_id) REFERENCES actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_statuses ADD CONSTRAINT FK_9C276E7780FF6630 FOREIGN KEY (responsible_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_statuses ADD CONSTRAINT FK_9C276E77BDAD1998 FOREIGN KEY (responsible_user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_task_statuses ADD CONSTRAINT FK_30C2A641B03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_task_statuses ADD CONSTRAINT FK_30C2A64157148967 FOREIGN KEY (action_task_id) REFERENCES action_tasks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_tasks ADD CONSTRAINT FK_B796A92B9D32F035 FOREIGN KEY (action_id) REFERENCES actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_tasks ADD CONSTRAINT FK_B796A92BC54C8C93 FOREIGN KEY (type_id) REFERENCES action_task_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_tasks ADD CONSTRAINT FK_B796A92B6BF700BD FOREIGN KEY (status_id) REFERENCES action_task_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_tasks ADD CONSTRAINT FK_B796A92BB03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_tasks ADD CONSTRAINT FK_B796A92B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_type_action_task_types ADD CONSTRAINT FK_81AC7D011FEE0472 FOREIGN KEY (action_type_id) REFERENCES action_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action_type_action_task_types ADD CONSTRAINT FK_81AC7D0146DF7259 FOREIGN KEY (action_task_type_id) REFERENCES action_task_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EFC54C8C93 FOREIGN KEY (type_id) REFERENCES action_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EF6BF700BD FOREIGN KEY (status_id) REFERENCES action_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EF59E53FB9 FOREIGN KEY (incident_id) REFERENCES incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EF80FF6630 FOREIGN KEY (responsible_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EFBDAD1998 FOREIGN KEY (responsible_user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EFB03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EF896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE actions_templates ADD CONSTRAINT FK_CD90AB496286964E FOREIGN KEY (incident_type_id) REFERENCES incident_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A59E53FB9 FOREIGN KEY (incident_id) REFERENCES incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A9D32F035 FOREIGN KEY (action_id) REFERENCES actions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AB03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A24FF092E FOREIGN KEY (target_group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE files ADD CONSTRAINT FK_6354059B03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_permissions ADD CONSTRAINT FK_855D3AEFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_permissions ADD CONSTRAINT FK_855D3AEFED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_statuses ADD CONSTRAINT FK_B3357A62B03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_statuses ADD CONSTRAINT FK_B3357A6259E53FB9 FOREIGN KEY (incident_id) REFERENCES incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_type_action_types ADD CONSTRAINT FK_C3D685806286964E FOREIGN KEY (incident_type_id) REFERENCES incident_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_type_action_types ADD CONSTRAINT FK_C3D685801FEE0472 FOREIGN KEY (action_type_id) REFERENCES action_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incidents ADD CONSTRAINT FK_E65135D0C54C8C93 FOREIGN KEY (type_id) REFERENCES incident_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incidents ADD CONSTRAINT FK_E65135D06BF700BD FOREIGN KEY (status_id) REFERENCES incident_statuses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incidents ADD CONSTRAINT FK_E65135D0B04C3708 FOREIGN KEY (repeated_incident_id) REFERENCES incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incidents ADD CONSTRAINT FK_E65135D0B03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incidents ADD CONSTRAINT FK_E65135D0896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_responsible_groups ADD CONSTRAINT FK_90559ABA59E53FB9 FOREIGN KEY (incident_id) REFERENCES incidents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident_responsible_groups ADD CONSTRAINT FK_90559ABAFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE locations ADD CONSTRAINT FK_17E64ABA727ACA70 FOREIGN KEY (parent_id) REFERENCES locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EF6BF700BD');
        $this->addSql('ALTER TABLE action_tasks DROP CONSTRAINT FK_B796A92B6BF700BD');
        $this->addSql('ALTER TABLE action_tasks DROP CONSTRAINT FK_B796A92BC54C8C93');
        $this->addSql('ALTER TABLE action_type_action_task_types DROP CONSTRAINT FK_81AC7D0146DF7259');
        $this->addSql('ALTER TABLE action_task_statuses DROP CONSTRAINT FK_30C2A64157148967');
        $this->addSql('ALTER TABLE action_type_action_task_types DROP CONSTRAINT FK_81AC7D011FEE0472');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EFC54C8C93');
        $this->addSql('ALTER TABLE incident_type_action_types DROP CONSTRAINT FK_C3D685801FEE0472');
        $this->addSql('ALTER TABLE action_statuses DROP CONSTRAINT FK_9C276E779D32F035');
        $this->addSql('ALTER TABLE action_tasks DROP CONSTRAINT FK_B796A92B9D32F035');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A9D32F035');
        $this->addSql('ALTER TABLE categories DROP CONSTRAINT FK_3AF34668727ACA70');
        $this->addSql('ALTER TABLE action_statuses DROP CONSTRAINT FK_9C276E7780FF6630');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EF80FF6630');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A24FF092E');
        $this->addSql('ALTER TABLE group_permissions DROP CONSTRAINT FK_855D3AEFE54D947');
        $this->addSql('ALTER TABLE incident_responsible_groups DROP CONSTRAINT FK_90559ABAFE54D947');
        $this->addSql('ALTER TABLE users_groups DROP CONSTRAINT FK_FF8AB7E0FE54D947');
        $this->addSql('ALTER TABLE incidents DROP CONSTRAINT FK_E65135D06BF700BD');
        $this->addSql('ALTER TABLE actions_templates DROP CONSTRAINT FK_CD90AB496286964E');
        $this->addSql('ALTER TABLE incident_type_action_types DROP CONSTRAINT FK_C3D685806286964E');
        $this->addSql('ALTER TABLE incidents DROP CONSTRAINT FK_E65135D0C54C8C93');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EF59E53FB9');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A59E53FB9');
        $this->addSql('ALTER TABLE incident_statuses DROP CONSTRAINT FK_B3357A6259E53FB9');
        $this->addSql('ALTER TABLE incidents DROP CONSTRAINT FK_E65135D0B04C3708');
        $this->addSql('ALTER TABLE incident_responsible_groups DROP CONSTRAINT FK_90559ABA59E53FB9');
        $this->addSql('ALTER TABLE locations DROP CONSTRAINT FK_17E64ABA727ACA70');
        $this->addSql('ALTER TABLE group_permissions DROP CONSTRAINT FK_855D3AEFED90CCA');
        $this->addSql('ALTER TABLE action_statuses DROP CONSTRAINT FK_9C276E77B03A8386');
        $this->addSql('ALTER TABLE action_statuses DROP CONSTRAINT FK_9C276E77BDAD1998');
        $this->addSql('ALTER TABLE action_task_statuses DROP CONSTRAINT FK_30C2A641B03A8386');
        $this->addSql('ALTER TABLE action_tasks DROP CONSTRAINT FK_B796A92BB03A8386');
        $this->addSql('ALTER TABLE action_tasks DROP CONSTRAINT FK_B796A92B896DBBDE');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EFBDAD1998');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EFB03A8386');
        $this->addSql('ALTER TABLE actions DROP CONSTRAINT FK_548F1EF896DBBDE');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AB03A8386');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A896DBBDE');
        $this->addSql('ALTER TABLE files DROP CONSTRAINT FK_6354059B03A8386');
        $this->addSql('ALTER TABLE incident_statuses DROP CONSTRAINT FK_B3357A62B03A8386');
        $this->addSql('ALTER TABLE incidents DROP CONSTRAINT FK_E65135D0B03A8386');
        $this->addSql('ALTER TABLE incidents DROP CONSTRAINT FK_E65135D0896DBBDE');
        $this->addSql('ALTER TABLE users_groups DROP CONSTRAINT FK_FF8AB7E0A76ED395');
        $this->addSql('DROP SEQUENCE action_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE action_task_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE action_task_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE action_tasks_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE action_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE actions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE actions_templates_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE categories_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE comments_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE files_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE group_permissions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE groups_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE incident_statuses_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE incident_types_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE incidents_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE locations_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE permissions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "users_id_seq" CASCADE');
        $this->addSql('DROP TABLE action_statuses');
        $this->addSql('DROP TABLE action_task_statuses');
        $this->addSql('DROP TABLE action_task_types');
        $this->addSql('DROP TABLE action_tasks');
        $this->addSql('DROP TABLE action_types');
        $this->addSql('DROP TABLE action_type_action_task_types');
        $this->addSql('DROP TABLE actions');
        $this->addSql('DROP TABLE actions_templates');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE files');
        $this->addSql('DROP TABLE group_permissions');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE incident_statuses');
        $this->addSql('DROP TABLE incident_types');
        $this->addSql('DROP TABLE incident_type_action_types');
        $this->addSql('DROP TABLE incidents');
        $this->addSql('DROP TABLE incident_responsible_groups');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE permissions');
        $this->addSql('DROP TABLE "users"');
        $this->addSql('DROP TABLE users_groups');
    }
}
