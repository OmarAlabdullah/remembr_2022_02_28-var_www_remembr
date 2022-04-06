<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131108122852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(100) NOT NULL, logins INT NOT NULL, lastLogin DATETIME DEFAULT NULL, confirmKey VARCHAR(40) DEFAULT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_2DA17977E7927C74 (email), UNIQUE INDEX UNIQ_2DA179775FBEA49E (confirmKey), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Instance (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, level INT DEFAULT NULL, parentId INT DEFAULT NULL, INDEX IDX_BB46D38810EE4CEE (parentId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE LogEntry (id INT AUTO_INCREMENT NOT NULL, time DATETIME NOT NULL, priority INT NOT NULL, priorityName VARCHAR(10) NOT NULL, message LONGTEXT NOT NULL, extra LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE UserRight (path VARCHAR(255) NOT NULL, user_id INT NOT NULL, rightGroup VARCHAR(255) NOT NULL, value INT NOT NULL, INDEX IDX_D05EE461A76ED395 (user_id), PRIMARY KEY(path, rightGroup, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Page (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, creationdate DATETIME DEFAULT NULL, publishdate DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Instance ADD CONSTRAINT FK_BB46D38810EE4CEE FOREIGN KEY (parentId) REFERENCES Instance (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE UserRight ADD CONSTRAINT FK_D05EE461A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE UserRight DROP FOREIGN KEY FK_D05EE461A76ED395");
        $this->addSql("ALTER TABLE Instance DROP FOREIGN KEY FK_BB46D38810EE4CEE");
        $this->addSql("DROP TABLE User");
        $this->addSql("DROP TABLE Instance");
        $this->addSql("DROP TABLE LogEntry");
        $this->addSql("DROP TABLE UserRight");
        $this->addSql("DROP TABLE Page");
    }
}
