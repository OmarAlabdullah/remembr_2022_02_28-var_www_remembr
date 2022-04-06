<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140128155719 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE cmsPage (id INT AUTO_INCREMENT NOT NULL, lang_id INT DEFAULT NULL, createDate DATETIME NOT NULL, updateDate DATETIME NOT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_B856C99FB213FA4 (lang_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE cmsLang (id INT AUTO_INCREMENT NOT NULL, lang VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE cmsPage ADD CONSTRAINT FK_B856C99FB213FA4 FOREIGN KEY (lang_id) REFERENCES cmsLang (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE cmsPage DROP FOREIGN KEY FK_B856C99FB213FA4");
        $this->addSql("DROP TABLE cmsPage");
        $this->addSql("DROP TABLE cmsLang");
    }
}
