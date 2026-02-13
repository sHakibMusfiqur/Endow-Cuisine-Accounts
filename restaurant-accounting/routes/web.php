<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ItemUsageRecipeController;
use App\Http\Controllers\InventoryReportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Storage route - Serve files without symlink (for shared hosting)
Route::get('/storage/{path}', function ($path) {
    return \App\Helpers\StorageHelper::serveFile($path);
})->where('path', '.*')->name('storage.file');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Email change verification route (accessible without authentication)
Route::get('/email-change/verify', [ProfileController::class, 'verifyEmail'])->name('email.change.verify');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard - All authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management - All authenticated users
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'editPassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.destroy-photo');
    Route::post('/profile/cancel-email-change', [ProfileController::class, 'cancelEmailChange'])->name('profile.cancel-email-change');

    // Transactions - Permission-based access control
    Route::middleware('can:view transactions')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');
    });

    Route::middleware('can:create transactions')->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        
        // Inventory Sale Routes - DEPRECATED: Redirect to Multi-Item
        Route::get('/transactions/inventory-sale', function () {
            return redirect()->route('transactions.inventory-sale-multi.create')
                ->with('info', 'Please use the Multi-Item Inventory Sale for better workflow.');
        })->name('transactions.inventory-sale.create');
        
        // Multi-Item Inventory Sale
        Route::get('/transactions/inventory-sale-multi', [TransactionController::class, 'createInventorySaleMulti'])->name('transactions.inventory-sale-multi.create');
        Route::post('/transactions/inventory-sale-multi', [TransactionController::class, 'storeInventorySaleMulti'])->name('transactions.inventory-sale-multi.store');
    });

    Route::middleware('can:view transactions')->group(function () {
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
            ->name('transactions.show');
    });

    Route::middleware('can:edit transactions')->group(function () {
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    });

    Route::middleware('can:delete transactions')->group(function () {
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

    // Categories - Permission-based (Admin only)
    Route::middleware('can:manage categories')->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // Payment Methods - Permission-based (Admin only)
    Route::middleware('can:manage payment methods')->group(function () {
        Route::resource('payment-methods', PaymentMethodController::class);
    });

    // Inventory Management - Permission-based
    // Note: Routes with specific paths (create, edit) must come BEFORE dynamic routes ({item})
    Route::prefix('inventory')->name('inventory.')->group(function () {
        
        // Manage Inventory Routes (Admin/Manager only)
        Route::middleware('can:manage inventory')->group(function () {
            // Inventory Items Management (specific routes first)
            Route::get('/items/create', [InventoryItemController::class, 'create'])->name('items.create');
            Route::post('/items', [InventoryItemController::class, 'store'])->name('items.store');
            Route::get('/items/{item}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
            Route::put('/items/{item}', [InventoryItemController::class, 'update'])->name('items.update');
            Route::delete('/items/{item}', [InventoryItemController::class, 'destroy'])->name('items.destroy');

            // Stock Movements Management
            Route::get('/movements/stock-in', [StockMovementController::class, 'createStockIn'])->name('movements.stock-in');
            Route::post('/movements/stock-in', [StockMovementController::class, 'storeStockIn'])->name('movements.stock-in.store');
            Route::get('/movements/stock-out', [StockMovementController::class, 'createStockOut'])->name('movements.stock-out');
            Route::post('/movements/stock-out', [StockMovementController::class, 'storeStockOut'])->name('movements.stock-out.store');
            
            // Internal Purchase - DEPRECATED: Redirect to Multi-Item
            Route::get('/movements/internal-purchase', function () {
                return redirect()->route('inventory.movements.internal-purchase-multi')
                    ->with('info', 'Please use the Multi-Item Internal Consumption for better workflow.');
            })->name('movements.internal-purchase');
            
            // Multi-Item Internal Purchase
            Route::get('/movements/internal-purchase-multi', [StockMovementController::class, 'createInternalPurchaseMulti'])->name('movements.internal-purchase-multi');
            Route::post('/movements/internal-purchase-multi', [StockMovementController::class, 'storeInternalPurchaseMulti'])->name('movements.internal-purchase-multi.store');

            // Usage Recipes Management
            Route::get('/recipes/create', [ItemUsageRecipeController::class, 'create'])->name('recipes.create');
            Route::post('/recipes', [ItemUsageRecipeController::class, 'store'])->name('recipes.store');
            Route::get('/recipes/{recipe}/edit', [ItemUsageRecipeController::class, 'edit'])->name('recipes.edit');
            Route::put('/recipes/{recipe}', [ItemUsageRecipeController::class, 'update'])->name('recipes.update');
            Route::delete('/recipes/{recipe}', [ItemUsageRecipeController::class, 'destroy'])->name('recipes.destroy');
        });

        // View Inventory Routes (All authenticated users with view permission)
        Route::middleware('can:view inventory')->group(function () {
            // Inventory Items (dynamic routes last)
            Route::get('/items', [InventoryItemController::class, 'index'])->name('items.index');
            Route::get('/items/low-stock', [InventoryItemController::class, 'lowStock'])->name('items.low-stock');
            Route::get('/items/{item}', [InventoryItemController::class, 'show'])->name('items.show');

            // Stock Movements - INDEX DISABLED (movements work internally for calculations)
            // Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
            // Redirect movements.index access to inventory items page
            Route::get('/movements', function () {
                return redirect()->route('inventory.items.index')->with('info', 'Stock movements are managed internally. Use Inventory Items to view stock levels.');
            })->name('movements.index');

            // Usage Recipes
            Route::get('/recipes', [ItemUsageRecipeController::class, 'index'])->name('recipes.index');

            // Reports
            Route::get('/reports', [InventoryReportController::class, 'index'])->name('reports.index');
            Route::post('/reports/export-csv', [InventoryReportController::class, 'exportCsv'])->name('reports.export-csv');
        });
    });

    // Currency Management - Permission-based (Admin only)
    Route::middleware('can:manage currencies')->group(function () {
        Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/currencies/{currency}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/currencies/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::patch('/currencies/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('currencies.setDefault');
        Route::patch('/currencies/{currency}/toggle-active', [CurrencyController::class, 'toggleActive'])->name('currencies.toggleActive');
        Route::get('/currencies/calculator', [CurrencyController::class, 'calculator'])->name('currencies.calculator');
        Route::post('/currencies/convert', [CurrencyController::class, 'convert'])->name('currencies.convert');
    });

    // Currency Switching - All authenticated users
    Route::post('/currency/switch', [CurrencyController::class, 'switchCurrency'])->name('currency.switch');

    // Reports - All authenticated users
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-summary', [ReportController::class, 'exportSummary'])->name('reports.export-summary');
    Route::get('/reports/summary-analysis', [ReportController::class, 'getSummaryAnalysis'])->name('reports.summary-analysis');

    // User Management - Admin only
    Route::middleware('can:manage users')->group(function () {
        Route::resource('users', UserManagementController::class);
        Route::get('/activity-logs', [UserManagementController::class, 'activityLogs'])->name('activity-logs.index');
    });
});
