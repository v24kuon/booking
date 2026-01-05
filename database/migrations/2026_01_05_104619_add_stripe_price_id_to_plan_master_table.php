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
        Schema::table('plan_master', function (Blueprint $table) {
            $table->string('stripe_price_id', 255)->nullable()->index();
        });

        $driver = DB::getDriverName();
        if (! in_array($driver, ['sqlite', 'mysql', 'pgsql'], true)) {
            return;
        }

        DB::table('plan_master')
            ->select(['plan_id', 'additional_info'])
            ->orderBy('plan_id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $planId = $row->plan_id ?? null;
                    if (! is_string($planId) || $planId === '') {
                        continue;
                    }

                    $stripePriceId = null;

                    $additional = $row->additional_info ?? null;
                    if (is_string($additional) && $additional !== '') {
                        try {
                            $decoded = json_decode($additional, true, 512, JSON_THROW_ON_ERROR);
                            if (is_array($decoded)) {
                                $value = $decoded['stripe_price_id'] ?? null;
                                if (is_string($value) && $value !== '') {
                                    $stripePriceId = $value;
                                }
                            }
                        } catch (\JsonException) {
                            // ignore invalid JSON (legacy / corrupted data)
                        }
                    }

                    if (! is_string($stripePriceId) || $stripePriceId === '') {
                        continue;
                    }

                    DB::table('plan_master')
                        ->where('plan_id', $planId)
                        ->update(['stripe_price_id' => $stripePriceId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Best-effort: move stripe_price_id back into additional_info before dropping the column.
        $driver = DB::getDriverName();
        if (in_array($driver, ['sqlite', 'mysql', 'pgsql'], true)) {
            DB::table('plan_master')
                ->select(['plan_id', 'additional_info', 'stripe_price_id'])
                ->whereNotNull('stripe_price_id')
                ->orderBy('plan_id')
                ->chunk(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $planId = $row->plan_id ?? null;
                        $stripePriceId = $row->stripe_price_id ?? null;

                        if (! is_string($planId) || $planId === '' || ! is_string($stripePriceId) || $stripePriceId === '') {
                            continue;
                        }

                        $additional = [];
                        $additionalRaw = $row->additional_info ?? null;
                        if (is_string($additionalRaw) && $additionalRaw !== '') {
                            try {
                                $decoded = json_decode($additionalRaw, true, 512, JSON_THROW_ON_ERROR);
                                if (is_array($decoded)) {
                                    $additional = $decoded;
                                }
                            } catch (\JsonException) {
                                // ignore invalid JSON (legacy / corrupted data)
                            }
                        }

                        $additional['stripe_price_id'] = $stripePriceId;

                        DB::table('plan_master')
                            ->where('plan_id', $planId)
                            ->update([
                                'additional_info' => json_encode($additional, JSON_UNESCAPED_UNICODE),
                            ]);
                    }
                });
        }

        Schema::table('plan_master', function (Blueprint $table) {
            $table->dropIndex(['stripe_price_id']);
            $table->dropColumn('stripe_price_id');
        });
    }
};
