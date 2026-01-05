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

        $indexIdentifier = $this->quoteQualifiedIdentifier($driver, $index);
        $tableIdentifier = $this->quoteQualifiedIdentifier($driver, $table);
        $columnsSql = implode(', ', array_map(
            fn (string $column): string => $this->quoteIdentifier($driver, $column),
            $columns
        ));

        // MySQL does not support "CREATE INDEX IF NOT EXISTS", so make this idempotent by catching errors.
        if ($driver === 'mysql') {
            try {
                DB::statement("CREATE UNIQUE INDEX {$indexIdentifier} ON {$tableIdentifier} ({$columnsSql})");
            } catch (QueryException $e) {
                // Ignore only "duplicate index name" (idempotent re-run). Do not swallow unexpected DB errors.
                if (! $this->isMysqlDuplicateIndexName($e)) {
                    throw $e;
                }
            }

            return;
        }

        // sqlite / pgsql
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS {$indexIdentifier} ON {$tableIdentifier} ({$columnsSql})");
    }

    private function dropIndex(string $driver, string $table, string $index): void
    {
        $indexIdentifier = $this->quoteQualifiedIdentifier($driver, $index);
        $tableIdentifier = $this->quoteQualifiedIdentifier($driver, $table);

        // MySQL does not support "DROP INDEX IF EXISTS", so make this idempotent by catching errors.
        if ($driver === 'mysql') {
            try {
                DB::statement("DROP INDEX {$indexIdentifier} ON {$tableIdentifier}");
            } catch (QueryException $e) {
                // Ignore only "can't drop key" (missing index). Do not swallow unexpected DB errors.
                if (! $this->isMysqlMissingIndex($e)) {
                    throw $e;
                }
            }

            return;
        }

        // sqlite / pgsql
        DB::statement("DROP INDEX IF EXISTS {$indexIdentifier}");
    }

    private function quoteIdentifier(string $driver, string $identifier): string
    {
        $quote = $driver === 'mysql' ? '`' : '"';

        return $quote.str_replace($quote, $quote.$quote, $identifier).$quote;
    }

    private function quoteQualifiedIdentifier(string $driver, string $identifier): string
    {
        return implode('.', array_map(
            fn (string $part): string => $this->quoteIdentifier($driver, $part),
            explode('.', $identifier)
        ));
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
