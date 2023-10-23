<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231023175759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Created ArticleReference entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE article_reference_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE article_reference (id INT NOT NULL, article_id INT NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_749619377294869C ON article_reference (article_id)');
        $this->addSql('ALTER TABLE article_reference ADD CONSTRAINT FK_749619377294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE article_reference_id_seq CASCADE');
        $this->addSql('ALTER TABLE article_reference DROP CONSTRAINT FK_749619377294869C');
        $this->addSql('DROP TABLE article_reference');
    }
}
