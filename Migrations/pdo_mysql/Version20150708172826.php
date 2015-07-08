<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/08 05:28:29
 */
class Version20150708172826 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            ADD registration_number VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD registration_number VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP registration_number
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            DROP registration_number
        ");
    }
}