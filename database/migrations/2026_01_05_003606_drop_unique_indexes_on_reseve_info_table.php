<?php

use App\MigrationIndexHelpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrationIndexHelpers;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('reseve_info')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        // Allow rebooking / retrial after cancellation:
        // replace strict unique constraints with status-aware unique constraints.
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_id_session_id_unique');
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_program_unique');

        // Enforce uniqueness only for ACTIVE reservations.
        // - One active reservation per member+session
        // - One active trial per member+program
        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_session_status_unique', [
            'member_id',
            'session_id',
            'reserve_status',
        ]);

        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_program_type_status_unique', [
            'member_id',
            'program_id',
            'reserve_type',
            'reserve_status',
        ]);
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

        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_session_status_unique');
        $this->dropIndex($driver, 'reseve_info', 'reseve_info_member_program_type_status_unique');

        // Restore the original strict unique constraints.
        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_id_session_id_unique', [
            'member_id',
            'session_id',
        ]);

        $this->createUniqueIndex($driver, 'reseve_info', 'reseve_info_member_program_unique', [
            'member_id',
            'program_id',
        ]);
    }
};
