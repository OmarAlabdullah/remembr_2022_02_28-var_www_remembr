<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140127162138 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE UserRight DROP FOREIGN KEY FK_D05EE461A76ED395");
        $this->addSql("DROP INDEX IDX_D05EE461A76ED395 ON UserRight");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE UserRight ADD CONSTRAINT FK_D05EE461A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("CREATE INDEX IDX_D05EE461A76ED395 ON UserRight (user_id)");
    }
}
