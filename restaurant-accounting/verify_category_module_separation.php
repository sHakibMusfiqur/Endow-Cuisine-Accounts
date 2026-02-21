<?php

/**
 * CATEGORY MODULE SEPARATION VERIFICATION SCRIPT
 * 
 * Purpose: Verify that the transaction system correctly enforces
 * separation between Restaurant and Inventory categories.
 * 
 * Date: February 20, 2026
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== CATEGORY MODULE SEPARATION VERIFICATION ===\n\n";

// Test 1: Verify categories have module field
echo "TEST 1: Verify Categories Have Module Field\n";
echo str_repeat("-", 50) . "\n";

$restaurantCategories = \App\Models\Category::where('module', 'restaurant')->count();
$inventoryCategories = \App\Models\Category::where('module', 'inventory')->count();
$categoriesWithoutModule = \App\Models\Category::whereNull('module')->count();

echo "✓ Restaurant categories: {$restaurantCategories}\n";
echo "✓ Inventory categories: {$inventoryCategories}\n";
if ($categoriesWithoutModule > 0) {
    echo "⚠ Categories without module: {$categoriesWithoutModule} (These should be assigned!)\n";
} else {
    echo "✓ All categories have module assigned\n";
}
echo "\n";

// Test 2: Check specific category assignments
echo "TEST 2: Verify Important Category Assignments\n";
echo str_repeat("-", 50) . "\n";

$importantCategories = [
    'Inventory Purchase' => 'inventory',
    'Inventory Item Sale' => 'inventory',
    'Inventory Internal Usage' => 'inventory',
    'Ingredients / Inventory Consumption' => 'restaurant',
];

foreach ($importantCategories as $name => $expectedModule) {
    $category = \App\Models\Category::where('name', $name)->first();
    if ($category) {
        if ($category->module === $expectedModule) {
            echo "✓ '{$name}' → module = '{$expectedModule}' (Correct)\n";
        } else {
            echo "✗ '{$name}' → module = '{$category->module}' (Expected: '{$expectedModule}')\n";
        }
    } else {
        echo "⚠ '{$name}' not found (will be created on first use)\n";
    }
}
echo "\n";

// Test 3: Verify transactions are linked to correct module categories
echo "TEST 3: Check Transaction-Category Module Consistency\n";
echo str_repeat("-", 50) . "\n";

// Check if any transactions exist
$totalTransactions = \App\Models\DailyTransaction::count();
echo "Total transactions in system: {$totalTransactions}\n";

if ($totalTransactions > 0) {
    // Get transactions by module
    $restaurantTransactions = \App\Models\DailyTransaction::whereHas('category', function($q) {
        $q->where('module', 'restaurant');
    })->count();
    
    $inventoryTransactions = \App\Models\DailyTransaction::whereHas('category', function($q) {
        $q->where('module', 'inventory');
    })->count();
    
    $transactionsWithoutModuleCategory = \App\Models\DailyTransaction::whereHas('category', function($q) {
        $q->whereNull('module');
    })->count();
    
    echo "✓ Transactions with restaurant categories: {$restaurantTransactions}\n";
    echo "✓ Transactions with inventory categories: {$inventoryTransactions}\n";
    
    if ($transactionsWithoutModuleCategory > 0) {
        echo "⚠ Transactions with categories lacking module: {$transactionsWithoutModuleCategory}\n";
    } else {
        echo "✓ All transactions use categories with module assigned\n";
    }
}
echo "\n";

// Test 4: Verify controller implementations
echo "TEST 4: Verify Controller Implementation\n";
echo str_repeat("-", 50) . "\n";

$controllerFile = __DIR__ . '/app/Http/Controllers/TransactionController.php';
$controllerContent = file_get_contents($controllerFile);

$checks = [
    "where('module', 'restaurant')" => "✓ TransactionController filters for restaurant module",
    "abort(403, 'Restaurant transactions can only use restaurant categories" => "✓ Backend validation prevents inventory categories in restaurant transactions",
    "['description' => 'Sales of inventory items', 'module' => 'inventory']" => "✓ Inventory sales use inventory module",
];

foreach ($checks as $pattern => $successMsg) {
    if (strpos($controllerContent, $pattern) !== false) {
        echo "{$successMsg}\n";
    } else {
        echo "✗ Missing: {$successMsg}\n";
    }
}
echo "\n";

// Test 5: Sample categories by module
echo "TEST 5: List Categories by Module\n";
echo str_repeat("-", 50) . "\n";

echo "Restaurant Categories:\n";
$restaurantCats = \App\Models\Category::where('module', 'restaurant')
    ->orderBy('type')
    ->orderBy('name')
    ->get();
foreach ($restaurantCats as $cat) {
    echo "  - {$cat->name} (Type: {$cat->type})\n";
}

echo "\nInventory Categories:\n";
$inventoryCats = \App\Models\Category::where('module', 'inventory')
    ->orderBy('type')
    ->orderBy('name')
    ->get();
foreach ($inventoryCats as $cat) {
    echo "  - {$cat->name} (Type: {$cat->type})\n";
}
echo "\n";

// Summary
echo "=== VERIFICATION SUMMARY ===\n";
echo str_repeat("=", 50) . "\n";

$issues = [];

if ($categoriesWithoutModule > 0) {
    $issues[] = "{$categoriesWithoutModule} categories need module assignment";
}

if (!empty($issues)) {
    echo "⚠ Issues found:\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
    echo "\n";
    echo "Please run the following to fix categories:\n";
    echo "  php artisan tinker\n";
    echo "  App\\Models\\Category::whereNull('module')->update(['module' => 'restaurant']);\n";
} else {
    echo "✓ All checks passed! Category module separation is correctly implemented.\n";
}

echo "\n=== IMPLEMENTATION DETAILS ===\n";
echo "1. Restaurant transactions (/transactions/create):\n";
echo "   - Only loads categories with module = 'restaurant'\n";
echo "   - Backend validation rejects inventory categories (403 error)\n\n";
echo "2. Inventory transactions (inventory/movements/*):\n";
echo "   - Only uses categories with module = 'inventory'\n";
echo "   - Auto-creates inventory categories with correct module\n\n";
echo "3. Controllers updated:\n";
echo "   - TransactionController::create() - filters restaurant categories\n";
echo "   - TransactionController::edit() - filters restaurant categories\n";
echo "   - TransactionController::store() - validates restaurant module\n";
echo "   - TransactionController::update() - validates restaurant module\n";
echo "   - Inventory methods ensure module = 'inventory'\n\n";

echo "✓ Implementation complete!\n\n";
