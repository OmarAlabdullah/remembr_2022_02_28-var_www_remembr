<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131230153618 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE PagePrivacySettings (id INT AUTO_INCREMENT NOT NULL, viewcondolence TINYINT(1) NOT NULL, addcondolence TINYINT(1) NOT NULL, viewmedia TINYINT(1) NOT NULL, addmedia TINYINT(1) NOT NULL, viewmessage TINYINT(1) NOT NULL, addmessage TINYINT(1) NOT NULL, contact TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Payment (id INT AUTO_INCREMENT NOT NULL, ddKey VARCHAR(255) NOT NULL, orderRef VARCHAR(35) NOT NULL, status INT NOT NULL, amtCurrency VARCHAR(255) NOT NULL, amtRegistered INT NOT NULL, amtPendingShopper INT NOT NULL, amtPendingAcquirer INT NOT NULL, amtApprovedAcquirer INT NOT NULL, amtCaptured INT NOT NULL, amtRefunded INT NOT NULL, amtChargedback INT NOT NULL, UNIQUE INDEX UNIQ_A295BD915348729F (ddKey), UNIQUE INDEX UNIQ_A295BD91C7FCD04B (orderRef), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Page ADD publicprivacysettings_id INT DEFAULT NULL, ADD userprivacysettings_id INT DEFAULT NULL, ADD friendprivacysettings_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191E2E747858 FOREIGN KEY (publicprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191EC3C6CFB3 FOREIGN KEY (userprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("ALTER TABLE Page ADD CONSTRAINT FK_B438191E6F2E1EB FOREIGN KEY (friendprivacysettings_id) REFERENCES PagePrivacySettings (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191E2E747858 ON Page (publicprivacysettings_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191EC3C6CFB3 ON Page (userprivacysettings_id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_B438191E6F2E1EB ON Page (friendprivacysettings_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191E2E747858");
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191EC3C6CFB3");
        $this->addSql("ALTER TABLE Page DROP FOREIGN KEY FK_B438191E6F2E1EB");
        $this->addSql("DROP TABLE PagePrivacySettings");
        $this->addSql("DROP TABLE Payment");
        $this->addSql("DROP INDEX UNIQ_B438191E2E747858 ON Page");
        $this->addSql("DROP INDEX UNIQ_B438191EC3C6CFB3 ON Page");
        $this->addSql("DROP INDEX UNIQ_B438191E6F2E1EB ON Page");
        $this->addSql("ALTER TABLE Page DROP publicprivacysettings_id, DROP userprivacysettings_id, DROP friendprivacysettings_id");
    }
}
