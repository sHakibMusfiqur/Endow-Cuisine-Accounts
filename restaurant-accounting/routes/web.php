<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ReportController;


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

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard - All authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

   

    // Transactions - Admin and Accountant can create/edit, Manager can only view
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    
    Route::middleware('role:admin,accountant')->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    });

    // Only admin can delete transactions
    Route::middleware('role:admin')->group(function () {
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

    // Categories - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // Payment Methods - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('payment-methods', PaymentMethodController::class);
    });

    // Currency Management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/currencies/{currency}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/currencies/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::patch('/currencies/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('currencies.setDefault');
        Route::patch('/currencies/{currency}/toggle-active', [CurrencyController::class, 'toggleActive'])->name('currencies.toggleActive');
        Route::get('/currencies/calculator', [CurrencyController::class, 'calculator'])->name('currencies.calculator');
        Route::post('/currencies/convert', [CurrencyController::class, 'convert'])->name('currencies.convert');
    });

    // Reports - All authenticated users
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-summary', [ReportController::class, 'exportSummary'])->name('reports.export-summary');
});
