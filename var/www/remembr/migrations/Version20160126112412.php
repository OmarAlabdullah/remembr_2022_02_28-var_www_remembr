<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160126112412 extends AbstractMigration
{
    private function existingImages() {
        $existing_images = $this->connection->fetchAll("SELECT id from `Image` WHERE location LIKE '/uploads/%' OR location LIKE '/images/%';");
        return array_map(function($x) { return $x['id']; }, $existing_images);
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE Page MODIFY photo_id int(11) NULL');
        $this->addSql("DELETE FROM `Image` WHERE location NOT LIKE '/uploads/%' AND location NOT LIKE '/images/%'");
        $existing_images = $this->existingImages();
        foreach ($this->connection->fetchAll("SELECT * from `Page`;") as $row)
            if (!in_array($row['photo_id'], $existing_images))
                $this->addSql('UPDATE Page SET photo_id = ? WHERE id = ?;', array(null, $row['id']));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $existing_images = $this->existingImages();
        $existing_images[] = 0;
        $new_photo_id = max($existing_images) + 1;
        
        foreach ($this->connection->fetchAll("SELECT * from `Page`;") as $row)
        {
            if ($row['photo_id'] === null)
            {
                $this->addSql('INSERT INTO Image (id, location) VALUES (?, ?);', array($new_photo_id, ''));
                $this->addSql('UPDATE Page SET photo_id = ? WHERE id = ?;', array($new_photo_id , $row['id']));
                $new_photo_id += 1;
            }   
            
        }
        $this->addSql('ALTER TABLE Page MODIFY photo_id int(11)');
    }
}
