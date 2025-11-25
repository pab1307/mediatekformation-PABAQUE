<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240513134621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // --- CATEGORIE ---
        $this->addSql(<<<'SQL'
CREATE TABLE categorie (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB
SQL);

        // --- PLAYLIST ---
        $this->addSql(<<<'SQL'
CREATE TABLE playlist (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB
SQL);

        // --- FORMATION ---
        $this->addSql(<<<'SQL'
CREATE TABLE formation (
    id INT AUTO_INCREMENT NOT NULL,
    playlist_id INT DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    title VARCHAR(100) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    video_id VARCHAR(20) DEFAULT NULL,
    INDEX IDX_404021BF6BBD148 (playlist_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB
SQL);

        // --- TABLE PIVOT formation_categorie ---
        $this->addSql(<<<'SQL'
CREATE TABLE formation_categorie (
    formation_id INT NOT NULL,
    categorie_id INT NOT NULL,
    INDEX IDX_830086E95200282E (formation_id),
    INDEX IDX_830086E9BCF5E72D (categorie_id),
    PRIMARY KEY(formation_id, categorie_id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB
SQL);

        // --- messenger_messages ---
        $this->addSql(<<<'SQL'
CREATE TABLE messenger_messages (
    id BIGINT AUTO_INCREMENT NOT NULL,
    body LONGTEXT NOT NULL,
    headers LONGTEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_75EA56E0FB7336F0 (queue_name),
    INDEX IDX_75EA56E0E3BD61CE (available_at),
    INDEX IDX_75EA56E016BA31DB (delivered_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB
SQL);

        // --- FOREIGN KEYS ---
        $this->addSql(<<<'SQL'
ALTER TABLE formation
    ADD CONSTRAINT FK_404021BF6BBD148
    FOREIGN KEY (playlist_id) REFERENCES playlist (id)
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE formation_categorie
    ADD CONSTRAINT FK_830086E95200282E
    FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE formation_categorie
    ADD CONSTRAINT FK_830086E9BCF5E72D
    FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema): void
    {
        // Order matters : drop relations then tables

        $this->addSql(<<<'SQL'
ALTER TABLE formation
    DROP FOREIGN KEY FK_404021BF6BBD148
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE formation_categorie
    DROP FOREIGN KEY FK_830086E95200282E
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE formation_categorie
    DROP FOREIGN KEY FK_830086E9BCF5E72D
SQL);

        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE formation_categorie');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
