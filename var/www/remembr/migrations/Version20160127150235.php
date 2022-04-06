<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160127150235 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE DraftPage (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, lastname VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, url VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, creationdate DATETIME DEFAULT NULL, publishdate DATETIME DEFAULT NULL, introtext LONGTEXT NOT NULL COLLATE utf8_unicode_ci, dateofbirth DATETIME DEFAULT NULL, dateofdeath DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, uselabels TINYINT(1) DEFAULT NULL, status VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, deletedAt DATETIME DEFAULT NULL, private TINYINT(1) DEFAULT NULL, type VARCHAR(16) DEFAULT NULL COLLATE utf8_unicode_ci, rotating TINYINT(1) DEFAULT NULL, gender VARCHAR(6) DEFAULT NULL COLLATE utf8_unicode_ci, country VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, residence VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, photo_id INT NULL, INDEX IDX_B438191EA76ED395 (user_id), INDEX photo_id (photo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE DraftPage');
    }
}
