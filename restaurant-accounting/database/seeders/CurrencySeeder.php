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
        
        $currencies = [
            [
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
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 1320.000000, // Placeholder - will be auto-updated
                'is_default' => false,
                'is_base' => false,
                'is_active' => true,
                'last_updated_at' => null, // Will be set when first updated
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BDT',
                'name' => 'Bangladeshi Taka',
                'symbol' => '৳',
                'exchange_rate' => 12.000000, // Placeholder - will be auto-updated
                'is_default' => false,
                'is_base' => false,
                'is_active' => true,
                'last_updated_at' => null, // Will be set when first updated
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('currencies')->insert($currencies);
        
        // Output reminder to update rates
        echo "\n✓ Currencies seeded successfully.\n";
        echo "⚠ IMPORTANT: Run 'php artisan currency:update-rates' to fetch real exchange rates.\n\n";
    }
}
