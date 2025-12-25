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
        Schema::create('id_sequences', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->unsignedBigInteger('next_number');
        });

        Schema::create('member_info', function (Blueprint $table) {
            $table->string('member_id', 8)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('member_name', 50);
            $table->string('member_tel', 20)->nullable();
            $table->string('member_mail', 50)->unique();
            $table->date('member_birth')->nullable();
            $table->string('member_password', 255);
            $table->json('additional_info')->nullable();
            $table->unsignedTinyInteger('status')->default(9);
        });

        Schema::create('program_master', function (Blueprint $table) {
            $table->string('program_id', 8)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('program_category', 50);
            $table->string('program_name', 100);
            $table->string('program_level', 50)->nullable();
            $table->string('program_overview', 500)->nullable();
            $table->string('program_detail', 500)->nullable();
            $table->decimal('program_price', 6, 0)->nullable();
            $table->integer('program_point')->nullable();
            $table->integer('program_ticket')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->json('additional_info')->nullable();
        });

        Schema::create('cource_master', function (Blueprint $table) {
            $table->string('cource_id', 8)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('cource_name', 100);
            $table->string('cource_category', 3)->nullable();
            $table->string('cource_level', 50)->nullable();
            $table->string('description', 500)->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });

        Schema::create('course_program', function (Blueprint $table) {
            $table->string('cource_id', 8);
            $table->string('program_category', 50);
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();

            $table->primary(['cource_id', 'program_category']);
            $table->foreign('cource_id')->references('cource_id')->on('cource_master');
        });

        Schema::create('location_master', function (Blueprint $table) {
            $table->string('location_id', 6)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('location_name', 100);
            $table->string('location_address', 200)->nullable();
            $table->string('location_tel', 20)->nullable();
            $table->string('location_mail', 50)->nullable();
            $table->string('description', 500)->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });

        Schema::create('location_img', function (Blueprint $table) {
            $table->increments('img_id');
            $table->string('location_id', 6);
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('img_path', 255);
            $table->unsignedTinyInteger('img_type')->default(0);

            $table->foreign('location_id')->references('location_id')->on('location_master');
            $table->index(['location_id', 'img_type']);
        });

        Schema::create('staff_master', function (Blueprint $table) {
            $table->string('staff_id', 7)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('staff_name', 100);
            $table->string('staff_gender', 10)->nullable();
            $table->unsignedTinyInteger('staff_age')->nullable();
            $table->string('licence_skill', 200)->nullable();
            $table->string('main_expertise', 200)->nullable();
            $table->string('staff_role', 100)->nullable();
            $table->string('description', 500)->nullable();
            $table->json('additional_info')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });

        Schema::create('staff_img', function (Blueprint $table) {
            $table->increments('img_id');
            $table->string('staff_id', 7);
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('img_path', 255);
            $table->unsignedTinyInteger('img_type')->default(0);

            $table->foreign('staff_id')->references('staff_id')->on('staff_master');
            $table->index(['staff_id', 'img_type']);
        });

        Schema::create('session', function (Blueprint $table) {
            $table->string('session_id', 10)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('program_id', 8);
            $table->string('location_id', 6);
            $table->string('staff_id', 7);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->unsignedSmallInteger('exp_capacity')->default(0);
            $table->unsignedSmallInteger('reserved_count')->default(0);
            $table->unsignedSmallInteger('reserved_exp_count')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->json('additional_info')->nullable();

            $table->foreign('program_id')->references('program_id')->on('program_master');
            $table->foreign('location_id')->references('location_id')->on('location_master');
            $table->foreign('staff_id')->references('staff_id')->on('staff_master');
            $table->index(['start_at']);
        });

        Schema::create('plan_master', function (Blueprint $table) {
            $table->string('plan_id', 8)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->unsignedTinyInteger('plan_type');
            $table->string('plan_name', 200);
            $table->string('cource_id', 8)->nullable();
            $table->unsignedSmallInteger('plan_usage_count')->default(0);
            $table->unsignedSmallInteger('plan_usage_date')->default(0);
            $table->decimal('plan_price', 6, 0)->nullable();
            $table->json('additional_info')->nullable();
            $table->unsignedTinyInteger('status')->default(1);

            $table->foreign('cource_id')->references('cource_id')->on('cource_master');
        });

        Schema::create('contract_info', function (Blueprint $table) {
            $table->string('contract_id', 10)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('member_id', 8);
            $table->string('plan_id', 8);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('plan_remain_count')->default(0);
            $table->date('plan_limit_date')->nullable();
            $table->unsignedTinyInteger('auto_renewal_flag')->default(9);
            $table->json('additional_info')->nullable();
            $table->unsignedTinyInteger('status')->default(9);

            $table->foreign('member_id')->references('member_id')->on('member_info');
            $table->foreign('plan_id')->references('plan_id')->on('plan_master');
            $table->index(['member_id']);
            $table->index(['plan_id']);
        });

        Schema::create('contract_event', function (Blueprint $table) {
            $table->increments('event_id');
            $table->timestamp('crt_time')->nullable();
            $table->string('event_type', 50);
            $table->string('contract_id', 10);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('plan_remain_count')->default(0);
            $table->date('plan_limit_date')->nullable();
            $table->unsignedTinyInteger('auto_renewal_flag')->default(9);
            $table->json('additional_info')->nullable();

            $table->foreign('contract_id')->references('contract_id')->on('contract_info');
            $table->index(['contract_id', 'event_type']);
        });

        Schema::create('deadline_master', function (Blueprint $table) {
            $table->unsignedTinyInteger('reserve_deadline');
            $table->unsignedTinyInteger('cancel_deadline');
            $table->unsignedTinyInteger('withdrowal_deadline');
        });

        Schema::create('additional_item_master', function (Blueprint $table) {
            $table->string('additional_item_type', 3)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->json('additional_item')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });

        Schema::create('label_setting', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('program_label', 100)->nullable();
            $table->string('session_label', 100)->nullable();
            $table->string('staff_label', 100)->nullable();
            $table->string('location_label', 100)->nullable();
            $table->string('reserve_label', 100)->nullable();
        });

        Schema::create('mail_info_master', function (Blueprint $table) {
            $table->string('mail_sender', 100)->nullable();
            $table->string('verified_mail_title', 100)->nullable();
            $table->text('verified_mail')->nullable();
            $table->string('registered_mail_title', 100)->nullable();
            $table->text('registered_mail')->nullable();
            $table->string('exp_reserved_mail_title', 100)->nullable();
            $table->text('exp_reserved_mail')->nullable();
            $table->string('paid_mail_title', 100)->nullable();
            $table->text('paid_mail')->nullable();
            $table->string('contracted_mail_title', 100)->nullable();
            $table->text('contracted_mail')->nullable();
            $table->string('reserved_mail_title', 100)->nullable();
            $table->text('reserved_mail')->nullable();
            $table->string('reserve_canceled_mail_title', 100)->nullable();
            $table->text('reserve_canceled_mail')->nullable();
            $table->string('withdrawn_mail_title', 100)->nullable();
            $table->text('withdrawn_mail')->nullable();
        });

        Schema::create('reseve_info', function (Blueprint $table) {
            $table->string('reserve_id', 9)->primary();
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('member_id', 8);
            $table->string('session_id', 10);
            $table->string('contract_id', 10)->nullable();
            $table->unsignedTinyInteger('reserve_payment');
            $table->unsignedTinyInteger('reserve_type');
            $table->string('channel', 30)->nullable();
            $table->unsignedTinyInteger('reserve_status')->default(1);
            $table->unsignedTinyInteger('payment_status')->default(1);
            $table->unsignedTinyInteger('attendance_status')->default(9);
            $table->dateTime('canceled_at')->nullable();
            $table->string('cancel_reason', 200)->nullable();
            $table->json('additional_info')->nullable();

            $table->foreign('member_id')->references('member_id')->on('member_info');
            $table->foreign('session_id')->references('session_id')->on('session');
            $table->foreign('contract_id')->references('contract_id')->on('contract_info');
            $table->unique(['member_id', 'session_id']);
            $table->index(['session_id']);
        });

        Schema::create('program_reputation_rule', function (Blueprint $table) {
            $table->increments('rule_id');
            $table->timestamp('crt_time')->nullable();
            $table->timestamp('upd_time')->nullable();
            $table->string('program_id', 8);
            $table->string('location_id', 6);
            $table->string('staff_id', 7);
            $table->unsignedTinyInteger('cycle_type');
            $table->string('day_of_week', 1)->nullable();
            $table->unsignedTinyInteger('week_of_month')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->unsignedSmallInteger('exp_capacity')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->json('additional_info')->nullable();

            $table->foreign('program_id')->references('program_id')->on('program_master');
            $table->foreign('location_id')->references('location_id')->on('location_master');
            $table->foreign('staff_id')->references('staff_id')->on('staff_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_reputation_rule');
        Schema::dropIfExists('reseve_info');
        Schema::dropIfExists('mail_info_master');
        Schema::dropIfExists('label_setting');
        Schema::dropIfExists('additional_item_master');
        Schema::dropIfExists('deadline_master');
        Schema::dropIfExists('contract_event');
        Schema::dropIfExists('contract_info');
        Schema::dropIfExists('plan_master');
        Schema::dropIfExists('session');
        Schema::dropIfExists('staff_img');
        Schema::dropIfExists('staff_master');
        Schema::dropIfExists('location_img');
        Schema::dropIfExists('location_master');
        Schema::dropIfExists('course_program');
        Schema::dropIfExists('cource_master');
        Schema::dropIfExists('program_master');
        Schema::dropIfExists('member_info');
        Schema::dropIfExists('id_sequences');
    }
};
