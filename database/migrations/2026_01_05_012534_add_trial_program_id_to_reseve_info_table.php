<?php

use App\MigrationIndexHelpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrationIndexHelpers;

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
};
