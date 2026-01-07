<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            ['name' => 'Cash', 'status' => 'active'],
            ['name' => 'Credit Card', 'status' => 'active'],
            ['name' => 'Debit Card', 'status' => 'active'],
            ['name' => 'Mobile Payment', 'status' => 'active'],
            ['name' => 'Bank Transfer', 'status' => 'active'],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
