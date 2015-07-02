<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/07/02 10:33:49
 */
class Version20150702223347 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_community (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_94CBDE605E237E06 (name), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_community_admins (
                community_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_54B66C0FFDA7B0BF (community_id), 
                INDEX IDX_54B66C0FA76ED395 (user_id), 
                PRIMARY KEY(community_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_pending_announcer (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                community_id INT DEFAULT NULL, 
                application_date DATETIME NOT NULL, 
                with_notification TINYINT(1) NOT NULL, 
                INDEX IDX_23308452A76ED395 (user_id), 
                INDEX IDX_23308452FDA7B0BF (community_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_announcer (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                community_id INT DEFAULT NULL, 
                with_notification TINYINT(1) NOT NULL, 
                INDEX IDX_C32B32A0A76ED395 (user_id), 
                INDEX IDX_C32B32A0FDA7B0BF (community_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_job_offer (
                id INT AUTO_INCREMENT NOT NULL, 
                community_id INT DEFAULT NULL, 
                announcer_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                code VARCHAR(255) DEFAULT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                offer VARCHAR(255) DEFAULT NULL, 
                original_name VARCHAR(255) DEFAULT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                establishment VARCHAR(255) DEFAULT NULL, 
                immersion TINYINT(1) NOT NULL, 
                discipline VARCHAR(255) DEFAULT NULL, 
                level VARCHAR(255) DEFAULT NULL, 
                duration VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_A721A41DFDA7B0BF (community_id), 
                INDEX IDX_A721A41D3EC97830 (announcer_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_job_request (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                community_id INT DEFAULT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                cv VARCHAR(255) DEFAULT NULL, 
                expiration_date DATETIME DEFAULT NULL, 
                original_name VARCHAR(255) DEFAULT NULL, 
                visible TINYINT(1) NOT NULL, 
                INDEX IDX_2759077BA76ED395 (user_id), 
                INDEX IDX_2759077BFDA7B0BF (community_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_community_admins 
            ADD CONSTRAINT FK_54B66C0FFDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_community_admins 
            ADD CONSTRAINT FK_54B66C0FA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            ADD CONSTRAINT FK_23308452A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            ADD CONSTRAINT FK_23308452FDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD CONSTRAINT FK_C32B32A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD CONSTRAINT FK_C32B32A0FDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            ADD CONSTRAINT FK_A721A41DFDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            ADD CONSTRAINT FK_A721A41D3EC97830 FOREIGN KEY (announcer_id) 
            REFERENCES formalibre_jobbundle_announcer (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_request 
            ADD CONSTRAINT FK_2759077BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_request 
            ADD CONSTRAINT FK_2759077BFDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_community_admins 
            DROP FOREIGN KEY FK_54B66C0FFDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_pending_announcer 
            DROP FOREIGN KEY FK_23308452FDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP FOREIGN KEY FK_C32B32A0FDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            DROP FOREIGN KEY FK_A721A41DFDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_request 
            DROP FOREIGN KEY FK_2759077BFDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_job_offer 
            DROP FOREIGN KEY FK_A721A41D3EC97830
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_community
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_community_admins
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_pending_announcer
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_announcer
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_job_offer
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_job_request
        ");
    }
}