<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131114163133 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE `Label` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, INDEX label_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE page_label (page_id INT NOT NULL, label_id INT NOT NULL, INDEX IDX_6826AF3EC4663E4 (page_id), INDEX IDX_6826AF3E33B92F39 (label_id), PRIMARY KEY(page_id, label_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Content (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT DEFAULT NULL, creationdate DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, photoid VARCHAR(255) DEFAULT NULL, videoid VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE memory_label (memory_id INT NOT NULL, label_id INT NOT NULL, INDEX IDX_E9B04E84CCC80CB3 (memory_id), INDEX IDX_E9B04E8433B92F39 (label_id), PRIMARY KEY(memory_id, label_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE page_label ADD CONSTRAINT FK_6826AF3EC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE page_label ADD CONSTRAINT FK_6826AF3E33B92F39 FOREIGN KEY (label_id) REFERENCES `Label` (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE memory_label ADD CONSTRAINT FK_E9B04E84CCC80CB3 FOREIGN KEY (memory_id) REFERENCES Content (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE memory_label ADD CONSTRAINT FK_E9B04E8433B92F39 FOREIGN KEY (label_id) REFERENCES `Label` (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Page CHANGE lastname lastname VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE page_label DROP FOREIGN KEY FK_6826AF3E33B92F39");
        $this->addSql("ALTER TABLE memory_label DROP FOREIGN KEY FK_E9B04E8433B92F39");
        $this->addSql("ALTER TABLE memory_label DROP FOREIGN KEY FK_E9B04E84CCC80CB3");
        $this->addSql("DROP TABLE `Label`");
        $this->addSql("DROP TABLE page_label");
        $this->addSql("DROP TABLE Content");
        $this->addSql("DROP TABLE memory_label");
        $this->addSql("ALTER TABLE Page CHANGE lastname lastname VARCHAR(255) NOT NULL");
    }
}
