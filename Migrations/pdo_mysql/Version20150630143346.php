<?php

namespace FormaLibre\JobBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/06/30 02:33:47
 */
class Version20150630143346 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_seeker (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                INDEX IDX_A0E12E2DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_announcer (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                community_id INT DEFAULT NULL, 
                INDEX IDX_C32B32A0A76ED395 (user_id), 
                INDEX IDX_C32B32A0FDA7B0BF (community_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_jobbundle_community (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
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
            ALTER TABLE formalibre_jobbundle_seeker 
            ADD CONSTRAINT FK_A0E12E2DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD CONSTRAINT FK_C32B32A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            ADD CONSTRAINT FK_C32B32A0FDA7B0BF FOREIGN KEY (community_id) 
            REFERENCES formalibre_jobbundle_community (id)
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_announcer 
            DROP FOREIGN KEY FK_C32B32A0FDA7B0BF
        ");
        $this->addSql("
            ALTER TABLE formalibre_jobbundle_community_admins 
            DROP FOREIGN KEY FK_54B66C0FFDA7B0BF
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_seeker
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_announcer
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_community
        ");
        $this->addSql("
            DROP TABLE formalibre_jobbundle_community_admins
        ");
    }
}