<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('program_id', 8)->nullable()->after('session_id');

            $table->foreign('program_id')->references('program_id')->on('program_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseve_info', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
