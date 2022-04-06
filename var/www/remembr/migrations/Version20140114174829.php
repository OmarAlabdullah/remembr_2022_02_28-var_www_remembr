<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140114174829 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE Comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, memory_id INT NOT NULL, createDate DATETIME NOT NULL, text VARCHAR(255) NOT NULL, INDEX IDX_5BC96BF0A76ED395 (user_id), INDEX IDX_5BC96BF0CCC80CB3 (memory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Comment ADD CONSTRAINT FK_5BC96BF0A76ED395 FOREIGN KEY (user_id) REFERENCES userAccount (id)");
        $this->addSql("ALTER TABLE Comment ADD CONSTRAINT FK_5BC96BF0CCC80CB3 FOREIGN KEY (memory_id) REFERENCES Content (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE Comment DROP FOREIGN KEY FK_5BC96BF0A76ED395");
        $this->addSql("DROP TABLE Comment");
    }
}
