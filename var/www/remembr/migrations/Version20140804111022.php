<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140804111022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191E2E747858");
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191E6F2E1EB");
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191EC3C6CFB3");
        $this->addSql("DROP INDEX UNIQ_B438191E2E747858 ON Page");
        $this->addSql("DROP INDEX UNIQ_B438191EC3C6CFB3 ON Page");
        $this->addSql("DROP INDEX UNIQ_B438191E6F2E1EB ON Page");
        $this->addSql("ALTER TABLE Page ADD private TINYINT(1) DEFAULT NULL, DROP publicprivacysettings_id, DROP friendprivacysettings_id, DROP userprivacysettings_id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Page ADD publicprivacysettings_id INT DEFAULT NULL, ADD friendprivacysettings_id INT DEFAULT NULL, ADD userprivacysettings_id INT DEFAULT NULL, DROP private");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191E2E747858 FOREIGN KEY (publicprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191E6F2E1EB FOREIGN KEY (friendprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191EC3C6CFB3 FOREIGN KEY (userprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191E2E747858 ON Page (publicprivacysettings_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191EC3C6CFB3 ON Page (userprivacysettings_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191E6F2E1EB ON Page (friendprivacysettings_id)");
    }
}
