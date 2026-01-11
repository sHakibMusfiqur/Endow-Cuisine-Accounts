<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,  // Must run first to set up roles and permissions
            UserSeeder::class,
            CurrencySeeder::class,  // Must run before transactions
            CategorySeeder::class,
            PaymentMethodSeeder::class,
        ]);
    }
}
