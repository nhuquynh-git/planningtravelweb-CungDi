<?php

namespace Tests\Support;

use Config\Database;
use RuntimeException;

final class DatabaseIsolation
{
    /**
     * @param list<string> $forbiddenExactNames
     */
    public static function assertSafe(Database $config, array $forbiddenExactNames = ['travel_planner_db']): void
    {
        if ($config->defaultGroup !== 'tests') {
            throw new RuntimeException('PHPUnit must use the tests database group, not the default connection.');
        }

        $tests    = $config->tests;
        $driver   = (string) ($tests['DBDriver'] ?? '');
        $database = (string) ($tests['database'] ?? '');
        $default  = (string) ($config->default['database'] ?? '');

        if ($driver !== 'MySQLi') {
            throw new RuntimeException('Integration tests must use MySQLi against isolated MySQL 8.4, not SQLite or the development DSN.');
        }

        if ($database === '' || $database === ':memory:') {
            throw new RuntimeException('The tests database name is missing.');
        }

        foreach ($forbiddenExactNames as $forbidden) {
            if (strcasecmp($database, $forbidden) === 0) {
                throw new RuntimeException('The tests group points at the developer database "' . $forbidden . '".');
            }
        }

        $systemSchemas = ['mysql', 'information_schema', 'performance_schema', 'sys'];
        if (in_array(strtolower($database), $systemSchemas, true)) {
            throw new RuntimeException('The tests group points at a system schema.');
        }

        if ($default !== '' && strcasecmp($database, $default) === 0) {
            throw new RuntimeException('The tests database must not reuse database.default.database.');
        }

        if (! str_contains(strtolower($database), 'test')) {
            throw new RuntimeException('The tests database name must include "test" so it cannot target development or production.');
        }
    }
}
