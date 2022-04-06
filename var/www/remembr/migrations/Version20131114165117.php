<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131114165117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Content ADD page_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Content ADD CONSTRAINT FK_31780935C4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)");
        $this->addSql("CREATE INDEX IDX_31780935C4663E4 ON Content (page_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Content DROP FOREIGN KEY FK_31780935C4663E4");
        $this->addSql("DROP INDEX IDX_31780935C4663E4 ON Content");
        $this->addSql("ALTER TABLE Content DROP page_id");
    }
}
