<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * Base case for MySQL integration tests.
 *
 * Uses the `tests` connection group only. Refuses to run against the
 * development database name or any database that does not include "test".
 */
abstract class DatabaseTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh   = true;
    protected $DBGroup   = 'tests';

    protected function setUp(): void
    {
        $this->assertIsolatedTestDatabase();
        parent::setUp();
    }

    protected function assertIsolatedTestDatabase(): void
    {
        DatabaseIsolation::assertSafe(config(Database::class));
    }
}
