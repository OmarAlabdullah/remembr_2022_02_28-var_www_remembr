<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140520124449 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE messageCentreInbox (id INT AUTO_INCREMENT NOT NULL, to_id INT DEFAULT NULL, message_id INT DEFAULT NULL, readDate DATETIME NOT NULL, reminded TINYINT(1) DEFAULT NULL, INDEX IDX_59FF6C4630354A65 (to_id), INDEX IDX_59FF6C46537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE messageCentreMessage (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(80) NOT NULL, content LONGTEXT NOT NULL, sendDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE messageCentreOutbox (id INT AUTO_INCREMENT NOT NULL, from_id INT DEFAULT NULL, message_id INT DEFAULT NULL, deleted TINYINT(1) DEFAULT NULL, INDEX IDX_AA7A574278CED90B (from_id), INDEX IDX_AA7A5742537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE messageCentreInbox ADD CONSTRAINT FK_59FF6C4630354A65 FOREIGN KEY (to_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE messageCentreInbox ADD CONSTRAINT FK_59FF6C46537A1329 FOREIGN KEY (message_id) REFERENCES messageCentreMessage (id)");
        $this->addSql("ALTER TABLE messageCentreOutbox ADD CONSTRAINT FK_AA7A574278CED90B FOREIGN KEY (from_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE messageCentreOutbox ADD CONSTRAINT FK_AA7A5742537A1329 FOREIGN KEY (message_id) REFERENCES messageCentreMessage (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE messageCentreInbox DROP FOREIGN KEY FK_59FF6C46537A1329");
        $this->addSql("ALTER TABLE messageCentreOutbox DROP FOREIGN KEY FK_AA7A5742537A1329");
        $this->addSql("DROP TABLE messageCentreInbox");
        $this->addSql("DROP TABLE messageCentreMessage");
        $this->addSql("DROP TABLE messageCentreOutbox");
    }
}
