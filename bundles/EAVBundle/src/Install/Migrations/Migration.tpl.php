<?= "<?php\n"; ?>

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class <?= $className ?> extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }


    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE eav_type (id UUID NOT NULL, alias VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_type_property (id UUID NOT NULL, type_id UUID NOT NULL, value_type SMALLINT NOT NULL, alias VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_entity (id UUID NOT NULL, type_id UUID NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE TABLE eav_values (id UUID NOT NULL, entity_id UUID NOT NULL, type_property_id UUID NOT NULL, value_text TEXT DEFAULT NULL, value_int INT DEFAULT NULL, value_decimal DECIMAL DEFAULT NULL, value_bool BOOLEAN DEFAULT NULL, value_datetime TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');

        // ENTITY RELATIONS
        $this->addSql('CREATE TABLE eav_entity_relation_type (id UUID NOT NULL, alias VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_entity_relation_type_restriction (id UUID NOT NULL, entity_relation_type_id UUID NOT NULL, restriction_type_code VARCHAR(255) NOT NULL, restriction JSONB DEFAULT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_entity_relation (id UUID NOT NULL, type_id UUID NOT NULL, from_id UUID NOT NULL, to_id UUID NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE eav_entity_relation ADD CONSTRAINT FK_eav_entity_relation_type FOREIGN KEY (type_id) REFERENCES eav_entity_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_entity_relation ADD CONSTRAINT FK_eav_entity_relation_from FOREIGN KEY (from_id) REFERENCES eav_entity (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_entity_relation ADD CONSTRAINT FK_eav_entity_relation_to FOREIGN KEY (to_id) REFERENCES eav_entity (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE eav_entity_relation_type_restriction ADD CONSTRAINT FK_eav_rtr_entity_relation_type_id FOREIGN KEY (entity_relation_type_id) REFERENCES eav_entity_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX IDX_eav_entity_relation_from_id ON eav_entity_relation (from_id)');
        $this->addSql('CREATE INDEX IDX_eav_entity_relation_to_id ON eav_entity_relation (to_id)');

        // TYPE RELATIONS
        $this->addSql('CREATE TABLE eav_type_relation_type (id UUID NOT NULL, alias VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_type_relation_type_restriction (id UUID NOT NULL, type_relation_type_id UUID NOT NULL, restriction_type_code VARCHAR(255) NOT NULL, restriction JSONB DEFAULT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_type_relation (id UUID NOT NULL, type_id UUID NOT NULL, from_id UUID NOT NULL, to_id UUID NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE eav_type_relation ADD CONSTRAINT FK_eav_type_relation_type FOREIGN KEY (type_id) REFERENCES eav_type_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_type_relation ADD CONSTRAINT FK_eav_type_relation_from FOREIGN KEY (from_id) REFERENCES eav_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_type_relation ADD CONSTRAINT FK_eav_type_relation_to FOREIGN KEY (to_id) REFERENCES eav_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE eav_type_relation_type_restriction ADD CONSTRAINT FK_eav_rtr_type_relation_type_id FOREIGN KEY (type_relation_type_id) REFERENCES eav_type_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX IDX_eav_type_relation_from_id ON eav_type_relation (from_id)');
        $this->addSql('CREATE INDEX IDX_eav_type_relation_to_id ON eav_type_relation (to_id)');

        // TYPE PROPERTY RELATIONS
        $this->addSql('CREATE TABLE eav_type_property_relation_type (id UUID NOT NULL, alias VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_type_property_relation_type_restriction (id UUID NOT NULL, type_property_relation_type_id UUID NOT NULL, restriction_type_code VARCHAR(255) NOT NULL, restriction JSONB DEFAULT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eav_type_property_relation (id UUID NOT NULL, type_id UUID NOT NULL, from_id UUID NOT NULL, to_id UUID NOT NULL, meta JSONB DEFAULT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE eav_type_property_relation ADD CONSTRAINT FK_eav_type_property_relation_type FOREIGN KEY (type_id) REFERENCES eav_type_property_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_type_property_relation ADD CONSTRAINT FK_eav_type_property_relation_from FOREIGN KEY (from_id) REFERENCES eav_type_property (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_type_property_relation ADD CONSTRAINT FK_eav_type_property_relation_to FOREIGN KEY (to_id) REFERENCES eav_type_property (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE eav_type_property_relation_type_restriction ADD CONSTRAINT FK_eav_rtr_type_property_relation_type_id FOREIGN KEY (type_property_relation_type_id) REFERENCES eav_type_property_relation_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX IDX_eav_type_property_relation_from_id ON eav_type_property_relation (from_id)');
        $this->addSql('CREATE INDEX IDX_eav_type_property_relation_to_id ON eav_type_property_relation (to_id)');

        // OTHER

        $this->addSql('ALTER TABLE eav_type_property ADD CONSTRAINT FK_eav_type_property_type FOREIGN KEY (type_id) REFERENCES eav_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE eav_entity ADD CONSTRAINT FK_eav_type_id FOREIGN KEY (type_id) REFERENCES eav_type (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE eav_values ADD CONSTRAINT FK_eav_values_entity_id FOREIGN KEY (entity_id) REFERENCES eav_entity (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eav_values ADD CONSTRAINT FK_eav_values_type_property_id FOREIGN KEY (type_property_id) REFERENCES eav_type_property (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE INDEX IDX_eav_type_alias ON eav_type (alias)');
        $this->addSql('CREATE INDEX IDX_eav_type_property_alias ON eav_type_property (alias)');

        $this->addSql('CREATE INDEX IDX_eav_value_text ON eav_values (value_text)');
        $this->addSql('CREATE INDEX IDX_eav_value_int ON eav_values (value_int)');
        $this->addSql('CREATE INDEX IDX_eav_value_decimal ON eav_values (value_decimal)');
        $this->addSql('CREATE INDEX IDX_eav_value_datetime ON eav_values (value_datetime)');
        $this->addSql('CREATE INDEX IDX_eav_value_bool ON eav_values (value_bool)');

    }


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE eav_entity_relation_type_restriction DROP CONSTRAINT FK_eav_rtr_entity_relation_type_id');
        $this->addSql('ALTER TABLE eav_type_relation_type_restriction DROP CONSTRAINT FK_eav_rtr_type_relation_type_id');
        $this->addSql('ALTER TABLE eav_type_property_relation_type_restriction DROP CONSTRAINT FK_eav_rtr_type_property_relation_type_id');

        $this->addSql('ALTER TABLE eav_type_property DROP CONSTRAINT FK_eav_type_property_type');
        $this->addSql('ALTER TABLE eav_entity DROP CONSTRAINT FK_eav_type_id');
        $this->addSql('ALTER TABLE eav_values DROP CONSTRAINT FK_eav_values_entity_id');
        $this->addSql('ALTER TABLE eav_values DROP CONSTRAINT FK_eav_values_type_property_id');

        $this->addSql('ALTER TABLE eav_entity_relation DROP CONSTRAINT FK_eav_entity_relation_type');
        $this->addSql('ALTER TABLE eav_entity_relation DROP CONSTRAINT FK_eav_entity_relation_from');
        $this->addSql('ALTER TABLE eav_entity_relation DROP CONSTRAINT FK_eav_entity_relation_to');

        $this->addSql('ALTER TABLE eav_type_relation DROP CONSTRAINT FK_eav_type_relation_type');
        $this->addSql('ALTER TABLE eav_type_relation DROP CONSTRAINT FK_eav_type_relation_from');
        $this->addSql('ALTER TABLE eav_type_relation DROP CONSTRAINT FK_eav_type_relation_to');

        $this->addSql('ALTER TABLE eav_type_property_relation DROP CONSTRAINT FK_eav_type_property_relation_type');
        $this->addSql('ALTER TABLE eav_type_property_relation DROP CONSTRAINT FK_eav_type_property_relation_from');
        $this->addSql('ALTER TABLE eav_type_property_relation DROP CONSTRAINT FK_eav_type_property_relation_to');
        
        $this->addSql('DROP TABLE eav_type');
        $this->addSql('DROP TABLE eav_type_property');
        $this->addSql('DROP TABLE eav_entity');
        $this->addSql('DROP TABLE eav_values');

        $this->addSql('DROP TABLE eav_entity_relation_type_restriction');
        $this->addSql('DROP TABLE eav_entity_relation_type');
        $this->addSql('DROP TABLE eav_entity_relation');

        $this->addSql('DROP TABLE eav_type_relation_type_restriction');
        $this->addSql('DROP TABLE eav_type_relation_type');
        $this->addSql('DROP TABLE eav_type_relation');

        $this->addSql('DROP TABLE eav_type_property_relation_type_restriction');
        $this->addSql('DROP TABLE eav_type_property_relation_type');
        $this->addSql('DROP TABLE eav_type_property_relation');
    }

}
