<?php

/**
 * CATEGORY MODULE VALIDATION EXAMPLES
 * 
 * This file demonstrates validation rules and examples for the module-based category system.
 */

namespace App\Examples;

use App\Models\Category;
use App\Models\DailyTransaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Validator;

class CategoryModuleValidationExamples
{
    /**
     * Example 1: Creating a Restaurant Category
     * 
     * Restaurant categories should be used for normal business operations
     * like sales, expenses, utilities, salary, etc.
     */
    public function exampleCreateRestaurantCategory()
    {
        $data = [
            'name' => 'Equipment Rental',
            'type' => 'expense',
            'module' => 'restaurant',
        ];

        // Validation rules
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:income,expense',
            'module' => 'required|in:restaurant,inventory',
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $category = Category::create($data);
        
        // Result: Category created with module='restaurant'
        // When used in transactions, source will automatically be 'restaurant'
        return [
            'success' => true,
            'category' => $category,
            'note' => 'This category will appear in Normal Reports only'
        ];
    }

    /**
     * Example 2: Creating an Inventory Category
     * 
     * Inventory categories should be used for inventory-specific operations
     * like stock purchases, inventory sales, stock adjustments, etc.
     */
    public function exampleCreateInventoryCategory()
    {
        $data = [
            'name' => 'Inventory Item Sale',
            'type' => 'income',
            'module' => 'inventory',
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:income,expense',
            'module' => 'required|in:restaurant,inventory',
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $category = Category::create($data);

        // Result: Category created with module='inventory'
        // When used in transactions, source will automatically be 'inventory'
        return [
            'success' => true,
            'category' => $category,
            'note' => 'This category will appear in Inventory Reports only'
        ];
    }

    /**
     * Example 3: Creating Transaction with Restaurant Category
     * 
     * When a transaction is created with a restaurant category,
     * the source is automatically set to 'restaurant'
     */
    public function exampleCreateRestaurantTransaction(TransactionService $transactionService)
    {
        // Assume we have a restaurant category with ID 5
        $restaurantCategory = Category::find(5); // module='restaurant'

        $transactionData = [
            'date' => '2026-02-19',
            'description' => 'Equipment rental payment',
            'transaction_type' => 'expense',
            'income' => 0,
            'expense' => 500000,
            'category_id' => $restaurantCategory->id,
            'payment_method_id' => 1,
            'currency_id' => 1,
        ];

        // TransactionService will automatically set source='restaurant'
        $transaction = $transactionService->createTransaction($transactionData);

        return [
            'success' => true,
            'transaction' => $transaction,
            'source' => $transaction->source, // Will be 'restaurant'
            'note' => 'This transaction appears in Normal Report'
        ];
    }

    /**
     * Example 4: Creating Transaction with Inventory Category
     * 
     * When a transaction is created with an inventory category,
     * the source is automatically set to 'inventory'
     */
    public function exampleCreateInventoryTransaction(TransactionService $transactionService)
    {
        // Assume we have an inventory category with ID 10
        $inventoryCategory = Category::find(10); // module='inventory'

        $transactionData = [
            'date' => '2026-02-19',
            'description' => 'Sold inventory item to customer',
            'transaction_type' => 'income',
            'income' => 100000,
            'expense' => 0,
            'category_id' => $inventoryCategory->id,
            'payment_method_id' => 1,
            'currency_id' => 1,
        ];

        // TransactionService will automatically set source='inventory'
        $transaction = $transactionService->createTransaction($transactionData);

        return [
            'success' => true,
            'transaction' => $transaction,
            'source' => $transaction->source, // Will be 'inventory'
            'note' => 'This transaction appears in Inventory Report'
        ];
    }

    /**
     * Example 5: Internal Inventory Consumption
     * 
     * Demonstrates dual-entry accounting for internal consumption
     * Creates TWO transactions with different modules
     */
    public function exampleInternalConsumption()
    {
        return [
            'transaction_1' => [
                'description' => 'Inventory internal usage – Rice (10 kg)',
                'source' => 'inventory', // Auto-set from category.module='inventory'
                'type' => 'income',
                'category' => 'Inventory Internal Usage (module=inventory)',
                'report' => 'Appears in Inventory Report'
            ],
            'transaction_2' => [
                'description' => 'Inventory consumed by restaurant – Rice',
                'source' => 'restaurant', // Auto-set from category.module='restaurant'
                'type' => 'expense',
                'category' => 'Ingredients / Inventory Consumption (module=restaurant)',
                'report' => 'Appears in Normal Report'
            ],
            'note' => 'Both transactions are linked via internal_reference_id'
        ];
    }

    /**
     * Example 6: Validation Error - Missing Module
     */
    public function exampleValidationErrorMissingModule()
    {
        $data = [
            'name' => 'Test Category',
            'type' => 'income',
            // Missing 'module' field
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:income,expense',
            'module' => 'required|in:restaurant,inventory',
        ]);

        // Validation will fail
        return [
            'error' => true,
            'message' => 'The module field is required.',
            'validator_errors' => $validator->errors()
        ];
    }

    /**
     * Example 7: Validation Error - Invalid Module
     */
    public function exampleValidationErrorInvalidModule()
    {
        $data = [
            'name' => 'Test Category',
            'type' => 'income',
            'module' => 'warehouse', // Invalid value
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:income,expense',
            'module' => 'required|in:restaurant,inventory',
        ]);

        // Validation will fail
        return [
            'error' => true,
            'message' => 'The selected module is invalid.',
            'validator_errors' => $validator->errors()
        ];
    }

    /**
     * Example 8: Querying Categories by Module
     */
    public function exampleQueryCategoriesByModule()
    {
        return [
            'restaurant_categories' => [
                'query' => Category::restaurant()->get(),
                'note' => 'Returns all categories where module=restaurant'
            ],
            'inventory_categories' => [
                'query' => Category::inventory()->get(),
                'note' => 'Returns all categories where module=inventory'
            ],
            'restaurant_income' => [
                'query' => Category::restaurant()->income()->get(),
                'note' => 'Returns restaurant income categories'
            ],
            'inventory_expense' => [
                'query' => Category::inventory()->expense()->get(),
                'note' => 'Returns inventory expense categories'
            ]
        ];
    }

    /**
     * Example 9: Report Filtering by Source
     */
    public function exampleReportFiltering()
    {
        return [
            'inventory_report' => [
                'filter' => "WHERE source = 'inventory'",
                'description' => 'Shows only inventory-related transactions',
                'includes' => [
                    'Stock purchases',
                    'Inventory sales',
                    'Stock adjustments',
                    'Internal usage (inventory side)'
                ]
            ],
            'normal_report' => [
                'filter' => "WHERE source = 'restaurant' OR source IS NULL",
                'description' => 'Shows only restaurant-related transactions',
                'includes' => [
                    'Food sales',
                    'Restaurant expenses',
                    'Utilities, Salary, Rent',
                    'Internal consumption (restaurant side)'
                ]
            ],
            'all_transactions' => [
                'filter' => 'No filter',
                'description' => 'Shows all transactions regardless of source'
            ]
        ];
    }

    /**
     * Example 10: Updating Category Module
     */
    public function exampleUpdateCategoryModule()
    {
        $category = Category::find(5);
        
        // Update module from 'restaurant' to 'inventory'
        $category->update(['module' => 'inventory']);

        return [
            'success' => true,
            'note' => 'Future transactions using this category will have source=inventory',
            'important' => 'Existing transactions are NOT affected - they keep their original source'
        ];
    }
}

/**
 * VALIDATION RULES SUMMARY
 * 
 * Category Creation/Update:
 * - name: required, string, max 255, unique
 * - type: required, must be 'income' or 'expense'
 * - module: required, must be 'restaurant' or 'inventory'
 * 
 * Automatic Behavior:
 * - When category.module = 'restaurant' → transaction.source = 'restaurant'
 * - When category.module = 'inventory' → transaction.source = 'inventory'
 * 
 * Report Filtering:
 * - Inventory Report: source = 'inventory'
 * - Normal Report: source = 'restaurant' OR source IS NULL
 * - All Transactions: no filter
 */
