<?php

namespace HeimrichHannot\EncoreBundle\Test\Migration;

use Doctrine\DBAL\DriverManager;
use HeimrichHannot\EncoreBundle\Migration\LayoutEncoreInheritanceMigration;
use PHPUnit\Framework\TestCase;

class LayoutEncoreInheritanceMigrationTest extends TestCase
{
    public function testMigrationPreservesExplicitChoicesAndEntries(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $migration = new LayoutEncoreInheritanceMigration($connection);
        $this->assertFalse($migration->shouldRun());
        $connection->executeStatement('CREATE TABLE tl_layout (id INTEGER PRIMARY KEY)');
        $this->assertFalse($migration->shouldRun());
        $connection->executeStatement("ALTER TABLE tl_layout ADD addEncore CHAR(1) NOT NULL DEFAULT 'i'");
        $connection->executeStatement('ALTER TABLE tl_layout ADD encoreEntries BLOB');
        $entries = serialize([['entry' => 'app', 'active' => '1']]);
        foreach (['', '1', '0', 'i'] as $id => $mode) {
            $connection->insert('tl_layout', ['id' => $id + 1, 'addEncore' => $mode, 'encoreEntries' => $entries]);
        }
        $this->assertTrue($migration->shouldRun());
        $this->assertTrue($migration->run()->isSuccessful());
        $this->assertSame(['0', '1', '0', 'i'], $connection->fetchFirstColumn('SELECT addEncore FROM tl_layout ORDER BY id'));
        $this->assertSame([$entries, $entries, $entries, $entries], $connection->fetchFirstColumn('SELECT encoreEntries FROM tl_layout ORDER BY id'));
        $this->assertFalse($migration->shouldRun());
        $migration->run();
        $connection->insert('tl_layout', ['id' => 5]);
        $this->assertSame('i', $connection->fetchOne('SELECT addEncore FROM tl_layout WHERE id = 5'));
        $this->assertFalse($migration->shouldRun());
        $connection->close();
    }
}
