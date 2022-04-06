<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140107104942 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE userAccount (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, hybridauth_session LONGTEXT DEFAULT NULL, created DATETIME NOT NULL, logins INT NOT NULL, lastLogin DATETIME DEFAULT NULL, confirmRequest DATETIME DEFAULT NULL, confirmKey VARCHAR(40) DEFAULT NULL, verified TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE userProfile (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_D28B6F2D9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE userAccess (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, provider VARCHAR(255) NOT NULL, auth_id VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, auth_secret VARCHAR(255) NOT NULL, created DATETIME NOT NULL, INDEX IDX_693F39A69B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE userProfile ADD CONSTRAINT FK_D28B6F2D9B6B5FBA FOREIGN KEY (account_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE userAccess ADD CONSTRAINT FK_693F39A69B6B5FBA FOREIGN KEY (account_id) REFERENCES userAccount (id)");
        $this->addSql("DROP TABLE Payment");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE userProfile DROP FOREIGN KEY FK_D28B6F2D9B6B5FBA");
        $this->addSql("ALTER TABLE userAccess DROP FOREIGN KEY FK_693F39A69B6B5FBA");
        $this->addSql("CREATE TABLE Payment (id INT AUTO_INCREMENT NOT NULL, ddKey VARCHAR(255) NOT NULL, orderRef VARCHAR(35) NOT NULL, status INT NOT NULL, amtCurrency VARCHAR(255) NOT NULL, amtRegistered INT NOT NULL, amtPendingShopper INT NOT NULL, amtPendingAcquirer INT NOT NULL, amtApprovedAcquirer INT NOT NULL, amtCaptured INT NOT NULL, amtRefunded INT NOT NULL, amtChargedback INT NOT NULL, UNIQUE INDEX UNIQ_A295BD915348729F (ddKey), UNIQUE INDEX UNIQ_A295BD91C7FCD04B (orderRef), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("DROP TABLE userAccount");
        $this->addSql("DROP TABLE userProfile");
        $this->addSql("DROP TABLE userAccess");
    }
}
