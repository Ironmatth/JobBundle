<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/07 09:21:18
 */
class Version20150707092117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD establishment VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            DROP establishment
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            ADD establishment VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP establishment
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            ADD establishment VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            DROP establishment
        ");
    }
}