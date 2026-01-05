<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reseve_info', function (Blueprint $table) {
            // Only used for trial reservation duplication guard (member_id x program_id).
            // Keep nullable so normal reservations are unaffected.
            $table->string('trial_program_id', 8)->nullable()->after('program_id');

            $table->foreign('trial_program_id')->references('program_id')->on('program_master');
        });

        // Backfill existing trial reservations.
        DB::statement('UPDATE reseve_info SET trial_program_id = program_id WHERE reserve_type = 2 AND program_id IS NOT NULL');

        $driver = Schema::getConnection()->getDriverName();

        // Replace the earlier (too strict) index which would also affect normal reservations when program_id is set.
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_program_type_status_unique');
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_trial_program_status_unique');

        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_trial_program_status_unique', [
            'member_id',
            'trial_program_id',
            'reserve_status',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_trial_program_status_unique');

        // Restore the previous status-aware index (NOTE: this will also affect normal reservations if program_id is set).
        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_program_type_status_unique', [
            'member_id',
            'program_id',
            'reserve_type',
            'reserve_status',
        ]);

        Schema::table('reseve_info', function (Blueprint $table) {
            $table->dropForeign(['trial_program_id']);
            $table->dropColumn('trial_program_id');
        });
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
