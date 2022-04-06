<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140128095022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE IF NOT EXISTS Payment (id INT AUTO_INCREMENT NOT NULL, ddKey VARCHAR(255) NOT NULL, orderRef VARCHAR(35) NOT NULL, status INT NOT NULL, amtCurrency VARCHAR(255) NOT NULL, amtRegistered INT NOT NULL, amtPendingShopper INT NOT NULL, amtPendingAcquirer INT NOT NULL, amtApprovedAcquirer INT NOT NULL, amtCaptured INT NOT NULL, amtRefunded INT NOT NULL, amtChargedback INT NOT NULL, UNIQUE INDEX UNIQ_A295BD915348729F (ddKey), UNIQUE INDEX UNIQ_A295BD91C7FCD04B (orderRef), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE Payment");
    }
}
