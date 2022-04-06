<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160104095539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        echo "Executing migration introducting a separate Image table for the photo of the Page table UP\n";
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE Image (id INT AUTO_INCREMENT NOT NULL, roi_offset_x INT DEFAULT NULL, roi_offset_y INT DEFAULT NULL, roi_width INT UNSIGNED DEFAULT NULL, roi_height INT UNSIGNED DEFAULT NULL, location VARCHAR(255) NOT NULL COLLATE latin1_swedish_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE Page ADD photo_id INT NOT NULL;');
        $this->addSql('CREATE INDEX photo_id ON Page (photo_id);');
        
        $photo_id = 1;
        foreach ($this->connection->fetchAll("SELECT * from `Page` WHERE photo IS NOT NULL") as $row)
        {
            $this->addSql('INSERT INTO Image (id, location) VALUES (?, ?);', array($photo_id, $row['photo']));
            $this->addSql('UPDATE Page SET photo_id = ? WHERE id = ?;', array($photo_id, $row['id']));
            $photo_id += 1;
        }
        $this->addSql('ALTER TABLE Page DROP COLUMN photo');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        echo "Executing migration introducting a separate Image table for the photo of the Page table DOWN\n";
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE Page ADD photo VARCHAR(255)');
        foreach ($this->connection->fetchAll("SELECT * from `Image`") as $row)
        {
            $this->addSql('UPDATE Page SET photo = ? WHERE photo_id = ?', array($row['location'], $row['id']));
        }
        
        $this->addSql('DROP TABLE Image');
        $this->addSql('ALTER TABLE Page DROP COLUMN photo_id');
    }
}
