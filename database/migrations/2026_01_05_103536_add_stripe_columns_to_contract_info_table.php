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
        Schema::table('contract_info', function (Blueprint $table) {
            $table->string('stripe_subscription_id', 255)->nullable()->index();
            $table->string('stripe_customer_id', 255)->nullable()->index();
            $table->string('stripe_price_id', 255)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_info', function (Blueprint $table) {
            $table->dropIndex(['stripe_subscription_id']);
            $table->dropIndex(['stripe_customer_id']);
            $table->dropIndex(['stripe_price_id']);

            $table->dropColumn(['stripe_subscription_id', 'stripe_customer_id', 'stripe_price_id']);
        });
    }
};
