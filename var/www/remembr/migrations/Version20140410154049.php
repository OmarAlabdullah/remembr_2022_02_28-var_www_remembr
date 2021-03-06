<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140410154049 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE userDashboardSettings (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, receivePageMessages TINYINT(1) DEFAULT NULL, receiveCommentMessages TINYINT(1) DEFAULT NULL, receivePrivateMessages TINYINT(1) DEFAULT NULL, receiveUpdates TINYINT(1) DEFAULT NULL, receiveTips TINYINT(1) DEFAULT NULL, mailFrequency VARCHAR(6) DEFAULT NULL, INDEX IDX_23E8F450A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE userDashboardSettings ADD CONSTRAINT FK_23E8F450A76ED395 FOREIGN KEY (user_id) REFERENCES userAccount (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE userDashboardSettings");
    }
}
