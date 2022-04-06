<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140604150457 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Notification (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, receiver_id INT DEFAULT NULL, sender_id INT DEFAULT NULL, memory_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, readDate DATETIME DEFAULT NULL, createDate DATETIME NOT NULL, deleted TINYINT(1) DEFAULT NULL, INDEX IDX_A765AD32C4663E4 (page_id), INDEX IDX_A765AD32CD53EDB6 (receiver_id), INDEX IDX_A765AD32F624B39D (sender_id), INDEX IDX_A765AD32CCC80CB3 (memory_id), INDEX IDX_A765AD32F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32C4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)");
        $this->addSql("ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32F624B39D FOREIGN KEY (sender_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32CCC80CB3 FOREIGN KEY (memory_id) REFERENCES Content (id)");
        $this->addSql("ALTER TABLE Notification ADD CONSTRAINT FK_A765AD32F8697D13 FOREIGN KEY (comment_id) REFERENCES Comment (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE Notification");
    }
}
