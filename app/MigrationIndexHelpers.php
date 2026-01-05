<?php

namespace App;

use Illuminate\Database\QueryException;
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
            } catch (QueryException $e) {
                // Ignore only "duplicate index name" (idempotent re-run). Do not swallow unexpected DB errors.
                if (! $this->isMysqlDuplicateIndexName($e)) {
                    throw $e;
                }
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
            } catch (QueryException $e) {
                // Ignore only "can't drop key" (missing index). Do not swallow unexpected DB errors.
                if (! $this->isMysqlMissingIndex($e)) {
                    throw $e;
                }
            }

            return;
        }

        // sqlite / pgsql
        DB::statement(sprintf('DROP INDEX IF EXISTS %s', $index));
    }

    private function isMysqlDuplicateIndexName(QueryException $e): bool
    {
        // ER_DUP_KEYNAME (1061): Duplicate key name '%s'
        return (int) ($e->errorInfo[1] ?? 0) === 1061;
    }

    private function isMysqlMissingIndex(QueryException $e): bool
    {
        // ER_CANT_DROP_FIELD_OR_KEY (1091): Can't DROP ... check that column/key exists
        return (int) ($e->errorInfo[1] ?? 0) === 1091;
    }
}
