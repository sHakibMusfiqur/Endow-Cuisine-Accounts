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
            // Income Categories
            ['name' => 'Food Sales', 'type' => 'income'],
            ['name' => 'Beverage Sales', 'type' => 'income'],
            ['name' => 'Catering Services', 'type' => 'income'],
            ['name' => 'Delivery Services', 'type' => 'income'],
            ['name' => 'Other Income', 'type' => 'income'],
            
            // Expense Categories
            ['name' => 'Food Supplies', 'type' => 'expense'],
            ['name' => 'Beverage Supplies', 'type' => 'expense'],
            ['name' => 'Utilities', 'type' => 'expense'],
            ['name' => 'Salary', 'type' => 'expense'],
            ['name' => 'Rent', 'type' => 'expense'],
            ['name' => 'Maintenance', 'type' => 'expense'],
            ['name' => 'Marketing', 'type' => 'expense'],
            ['name' => 'Transportation', 'type' => 'expense'],
            ['name' => 'Equipment', 'type' => 'expense'],
            ['name' => 'Other Expenses', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
