<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131119112225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Banner (id INT AUTO_INCREMENT NOT NULL, format_id INT DEFAULT NULL, externalId VARCHAR(40) NOT NULL, enabled TINYINT(1) NOT NULL, type INT NOT NULL, content LONGTEXT NOT NULL, url TINYTEXT NOT NULL, views INT NOT NULL, maxViews INT NOT NULL, clicks INT NOT NULL, maxClicks INT NOT NULL, UNIQUE INDEX UNIQ_6831BDD1A770AC6E (externalId), INDEX IDX_6831BDD1D629F605 (format_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE BannerFormat (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(40) NOT NULL, width INT NOT NULL, height INT NOT NULL, cssClass VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Banner ADD CONSTRAINT FK_6831BDD1D629F605 FOREIGN KEY (format_id) REFERENCES BannerFormat (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Banner DROP FOREIGN KEY FK_6831BDD1D629F605");
        $this->addSql("DROP TABLE Banner");
        $this->addSql("DROP TABLE BannerFormat");
    }
}
