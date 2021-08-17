<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210815174730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP roles, DROP is_admin, CHANGE email email VARCHAR(255) NOT NULL, CHANGE token username VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD last_name VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD roles JSON NOT NULL, ADD is_admin TINYINT(1) NOT NULL, CHANGE email email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE username token VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
