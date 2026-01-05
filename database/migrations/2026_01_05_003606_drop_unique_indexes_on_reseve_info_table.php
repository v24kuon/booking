<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('reseve_info')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        // Allow rebooking / retrial after cancellation by removing strict unique constraints.
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_id_session_id_unique');
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_program_unique');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('reseve_info')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_id_session_id_unique', ['member_id', 'session_id']);
        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_program_unique', ['member_id', 'program_id']);
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function createUniqueIndex(string $driver, string $table, string $index, array $columns): void
    {
        if (! Schema::hasColumns($table, $columns)) {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(sprintf(
                'CREATE UNIQUE INDEX %s ON %s (%s)',
                $index,
                $table,
                implode(', ', $columns)
            ));

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
};
