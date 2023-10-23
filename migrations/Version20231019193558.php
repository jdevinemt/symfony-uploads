<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231019193558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renamed article.filename to article.image_filename.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article RENAME COLUMN filename TO image_filename');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article RENAME COLUMN image_filename TO filename');
    }
}
