<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150323163510 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE uploadedImage (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(5) NOT NULL, slug VARCHAR(255) NOT NULL, alternative VARCHAR(255) NOT NULL, createDate DATETIME NOT NULL, updateDate DATETIME NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE PagePrivacySettings');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE PagePrivacySettings (id INT AUTO_INCREMENT NOT NULL, viewcondolence TINYINT(1) NOT NULL, addcondolence TINYINT(1) NOT NULL, viewmedia TINYINT(1) NOT NULL, addmedia TINYINT(1) NOT NULL, viewmessage TINYINT(1) NOT NULL, addmessage TINYINT(1) NOT NULL, contact TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE uploadedImage');
    }
}
