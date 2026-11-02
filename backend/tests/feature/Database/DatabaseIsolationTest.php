<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\DatabaseIsolation;

/**
 * @internal
 */
final class DatabaseIsolationTest extends CIUnitTestCase
{
    public function testPhpunitUsesIsolatedMysqlTestDatabase(): void
    {
        $config = config(Database::class);

        DatabaseIsolation::assertSafe($config);

        $this->assertSame('tests', $config->defaultGroup);
        $this->assertSame('MySQLi', $config->tests['DBDriver']);
        $this->assertNotSame($config->default['database'], $config->tests['database']);
        $this->assertStringContainsString('test', strtolower((string) $config->tests['database']));

        $db   = db_connect('tests');
        $name = $db->query('SELECT DATABASE() AS name')->getRow()->name;

        $this->assertSame($config->tests['database'], $name);
        $this->assertNotSame('travel_planner_db', $name);
    }
}
