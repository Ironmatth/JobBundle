<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/06 06:09:28
 */
class Version20150706180927 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_seeker (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                community_id INT DEFAULT NULL, 
                registration_number VARCHAR(255) NOT NULL, 
                INDEX IDX_A0E12E2DA76ED395 (user_id), 
                INDEX IDX_A0E12E2DFDA7B0BF (community_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_seeker 
            ADD CONSTRAINT FK_A0E12E2DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_seeker 
            ADD CONSTRAINT FK_A0E12E2DFDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            ADD fase_number VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD fase_number VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_jobbundle_seeker
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP fase_number
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            DROP fase_number
        ");
    }
}