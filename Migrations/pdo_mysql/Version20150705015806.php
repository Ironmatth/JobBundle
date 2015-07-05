<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/05 01:58:06
 */
class Version20150705015806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            ADD creation_date DATETIME NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_request 
            ADD creation_date DATETIME NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            DROP creation_date
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_request 
            DROP creation_date
        ");
    }
}