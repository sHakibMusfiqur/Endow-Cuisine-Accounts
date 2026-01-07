<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates existing transactions to have currency support.
     * It sets all existing transactions to use the default currency (KRW).
     * Note: This will be skipped if currencies haven't been seeded yet.
     * Run "php artisan db:seed --class=CurrencySeeder" then this migration will work.
     */
    public function up(): void
    {
        // Get the default currency ID
        $defaultCurrencyId = DB::table('currencies')
            ->where('is_default', true)
            ->value('id');

        // Skip if no currencies exist yet (will be handled by seeder or manually)
        if (!$defaultCurrencyId) {
            // Don't throw exception - this is expected on fresh migration
            // Currencies will be seeded after migrations
            return;
        }

        // Update existing transactions only if we have a default currency
        DB::table('daily_transactions')
            ->whereNull('currency_id')
            ->update([
                'currency_id' => $defaultCurrencyId,
                'amount_original' => DB::raw('CASE WHEN income > 0 THEN income ELSE expense END'),
                'amount_base' => DB::raw('CASE WHEN income > 0 THEN income ELSE expense END'),
                'exchange_rate_snapshot' => 1.000000,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset currency fields for all transactions
        DB::table('daily_transactions')->update([
            'currency_id' => null,
            'amount_original' => 0,
            'amount_base' => 0,
            'exchange_rate_snapshot' => 1.000000,
        ]);
    }
};
