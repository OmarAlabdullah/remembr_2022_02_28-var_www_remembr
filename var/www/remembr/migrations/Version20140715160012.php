<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140715160012 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Page ADD status VARCHAR(16) NOT NULL, ADD deletedAt DATETIME DEFAULT NULL");
		$this->addSql("UPDATE Page SET status='published'");

		$this->addSql("CREATE TABLE ConfirmAction (id INT AUTO_INCREMENT NOT NULL, confirmkey VARCHAR(32) NOT NULL, action VARCHAR(32) DEFAULT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', creationdate DATETIME NOT NULL, expirationdate DATETIME NOT NULL, INDEX key_idx (confirmkey), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DROP TABLE ConfirmAction");
		
        $this->addSql("ALTER TABLE Page DROP status, DROP deletedAt");
    }
}
