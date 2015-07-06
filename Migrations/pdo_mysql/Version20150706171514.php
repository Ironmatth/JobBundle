<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/06 05:15:14
 */
class Version20150706171514 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_province (
                id INT AUTO_INCREMENT NOT NULL, 
                translationKey VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD province_id INT DEFAULT NULL, 
            ADD adress VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD CONSTRAINT FK_C32B32A0E946114A FOREIGN KEY (province_id) 
            REFERENCES formalibre_jobbundle_province (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_C32B32A0E946114A ON formalibre_jobbundle_announcer (province_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP FOREIGN KEY FK_C32B32A0E946114A
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_province
        ");
        $this->addSql("
            DROP INDEX IDX_C32B32A0E946114A ON formalibre_jobbundle_announcer
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP province_id, 
            DROP adress
        ");
    }
}