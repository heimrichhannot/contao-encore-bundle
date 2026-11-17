<?php

declare(strict_types=1);

namespace HeimrichHannot\EncoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class LayoutEncoreInheritanceMigration extends AbstractMigration
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist(['tl_layout'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_layout');
        if (!isset($columns['addencore'])) {
            return false;
        }

        return false !== $this->connection->fetchOne("SELECT id FROM tl_layout WHERE addEncore = '' LIMIT 1");
    }

    public function run(): MigrationResult
    {
        $this->connection->update('tl_layout', [
            'addEncore' => '0',
        ], [
            'addEncore' => '',
        ]);

        return $this->createResult(true);
    }
}
