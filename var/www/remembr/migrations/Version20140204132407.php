<?php
namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140204132407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Content ADD user_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Content ADD CONSTRAINT FK_31780935A76ED395 FOREIGN KEY (user_id) REFERENCES userAccount (id)");
        $this->addSql("CREATE INDEX IDX_31780935A76ED395 ON Content (user_id)");
        $this->addSql("ALTER TABLE Page ADD user_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191EA76ED395 FOREIGN KEY (user_id) REFERENCES userAccount (id)");
        $this->addSql("CREATE INDEX IDX_B438191EA76ED395 ON Page (user_id)");
        $this->addSql("UPDATE Page SET user_id = (SELECT id FROM userAccount LIMIT 1)");
        $this->addSql("UPDATE Content SET user_id = (SELECT id FROM userAccount LIMIT 1)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Content DROP FOREIGN KEY FK_31780935A76ED395");
        $this->addSql("DROP INDEX IDX_31780935A76ED395 ON Content");
        $this->addSql("ALTER TABLE Content DROP user_id");
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191EA76ED395");
        $this->addSql("DROP INDEX IDX_B438191EA76ED395 ON Page");
        $this->addSql("ALTER TABLE Page DROP user_id");
    }
}
