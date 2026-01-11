<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: Exchange rates are placeholder values.
     * Run 'php artisan currency:update-rates' after seeding to get real rates.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Only KRW currency for Endow Cuisine Accounting System
        $currency = [
            'code' => 'KRW',
            'name' => 'Korean Won',
            'symbol' => '₩',
            'exchange_rate' => 1.000000, // Base currency - always 1
            'is_default' => true,
            'is_base' => true,
            'is_active' => true,
            'last_updated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('currencies')->insert($currency);

        echo "\n✓ Currency seeded successfully: KRW (Korean Won)\n";
        echo "✓ System configured for single currency operation.\n\n";
    }
}
