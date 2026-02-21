<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Income Categories (Restaurant Module)
            ['name' => 'Food Sales', 'type' => 'income', 'module' => 'restaurant'],
            ['name' => 'Beverage Sales', 'type' => 'income', 'module' => 'restaurant'],
            ['name' => 'Catering Services', 'type' => 'income', 'module' => 'restaurant'],
            ['name' => 'Delivery Services', 'type' => 'income', 'module' => 'restaurant'],
            ['name' => 'Other Income', 'type' => 'income', 'module' => 'restaurant'],

            // Expense Categories (Restaurant Module)
            ['name' => 'Food Supplies', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Beverage Supplies', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Utilities', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Salary', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Rent', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Maintenance', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Marketing', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Transportation', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Equipment', 'type' => 'expense', 'module' => 'restaurant'],
            ['name' => 'Other Expenses', 'type' => 'expense', 'module' => 'restaurant'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
