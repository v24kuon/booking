<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait MigrationIndexHelpers
{
    /**
     * @param  array<int, string>  $columns
     */
    private function createUniqueIndex(string $driver, string $table, string $index, array $columns): void
    {
        if (! Schema::hasColumns($table, $columns)) {
            return;
        }

        // MySQL does not support "CREATE INDEX IF NOT EXISTS", so make this idempotent by catching errors.
        if ($driver === 'mysql') {
            try {
                DB::statement(sprintf(
                    'CREATE UNIQUE INDEX %s ON %s (%s)',
                    $index,
                    $table,
                    implode(', ', $columns)
                ));
            } catch (\Throwable) {
                // ignore if already exists / unsupported
            }

            return;
        }

        // sqlite / pgsql
        DB::statement(sprintf(
            'CREATE UNIQUE INDEX IF NOT EXISTS %s ON %s (%s)',
            $index,
            $table,
            implode(', ', $columns)
        ));
    }

    private function dropIndex(string $driver, string $table, string $index): void
    {
        // MySQL does not support "DROP INDEX IF EXISTS", so make this idempotent by catching errors.
        if ($driver === 'mysql') {
            try {
                DB::statement(sprintf('DROP INDEX %s ON %s', $index, $table));
            } catch (\Throwable) {
                // ignore if missing
            }

            return;
        }

        // sqlite / pgsql
        DB::statement(sprintf('DROP INDEX IF EXISTS %s', $index));
    }
}
