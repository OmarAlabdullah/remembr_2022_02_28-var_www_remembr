<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140129121002 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE cmsPage DROP FOREIGN KEY FK_B856C99FB213FA4");
        $this->addSql("DROP INDEX IDX_B856C99FB213FA4 ON cmsPage");
        $this->addSql("ALTER TABLE cmsPage ADD lang VARCHAR(5) NOT NULL");
        $this->addSql("UPDATE cmsPage SET lang = (SELECT lang FROM cmsLang WHERE cmsLang.id = lang_id LIMIT 1)");
        $this->addSql("ALTER TABLE cmsPage DROP lang_id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE cmsPage ADD lang_id INT DEFAULT NULL, DROP lang");
        $this->addSql("ALTER TABLE cmsPage ADD CONSTRAINT FK_B856C99FB213FA4 FOREIGN KEY (lang_id) REFERENCES cmsLang (id)");
        $this->addSql("CREATE INDEX IDX_B856C99FB213FA4 ON cmsPage (lang_id)");
    }
}
