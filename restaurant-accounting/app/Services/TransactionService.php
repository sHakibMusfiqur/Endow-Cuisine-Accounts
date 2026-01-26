<?php

namespace App\Services;

use App\Models\DailyTransaction;
use App\Models\Currency;
use App\Models\Category;
use App\Models\ItemUsageRecipe;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction with balance calculation.
     * 
     * CRITICAL: Dynamic Rate Conversion & Historical Locking
     * =======================================================
     * 1. Fetches CURRENT rate from database (dynamic)
     * 2. Converts to base currency (KRW) using multiplication
     * 3. Stores rate SNAPSHOT with transaction (historical lock)
     * 4. Future rate changes DO NOT affect this transaction
     * 
     * Example:
     *   User enters: 10 USD
     *   Current rate: 1320.50 KRW/USD (fetched dynamically from DB)
     *   Conversion: 10 × 1320.50 = 13,205 KRW
     *   Stored: original=10, base=13205, rate_snapshot=1320.50
     *   
     * If rate changes tomorrow to 1400, this transaction stays 13,205 KRW ✓
     *
     * @param array $data
     * @return DailyTransaction
     * @throws Exception
     */
    public function createTransaction(array $data): DailyTransaction
    {
        // Validate that income and expense are not both greater than 0
        if ($data['income'] > 0 && $data['expense'] > 0) {
            throw new Exception('Income and expense cannot both be greater than 0 in one transaction.');
        }

        // Validate that at least one of income or expense is greater than 0
        if ($data['income'] <= 0 && $data['expense'] <= 0) {
            throw new Exception('Either income or expense must be greater than 0.');
        }

        DB::beginTransaction();

        try {
            // STEP 1: Get currency (default to KRW if not specified)
            $currencyId = $data['currency_id'] ?? Currency::getDefault()?->id;
            $currency = Currency::findOrFail($currencyId);
            
            // STEP 2: Determine original amount (the amount in selected currency)
            $amountOriginal = $data['income'] > 0 ? $data['income'] : $data['expense'];
            
            // STEP 3: Convert to base currency (KRW) using CURRENT rate (dynamic)
            // CRITICAL: Uses convertToBase() which multiplies (never divides)
            $amountBase = $currency->convertToBase($amountOriginal);
            
            // STEP 4: Store exchange rate SNAPSHOT for historical accuracy
            // This locks the rate - future updates won't change this transaction
            $exchangeRateSnapshot = $currency->exchange_rate;
            
            // Calculate income and expense in base currency
            $incomeBase = $data['income'] > 0 ? $amountBase : 0;
            $expenseBase = $data['expense'] > 0 ? $amountBase : 0;
            
            // Calculate balance
            $lastBalance = $this->getLastBalance($data['date']);
            $newBalance = $lastBalance + $incomeBase - $expenseBase;

            // Create transaction
            $transaction = DailyTransaction::create([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'balance' => $newBalance,
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $currencyId,
                'amount_original' => $amountOriginal,
                'amount_base' => $amountBase,
                'exchange_rate_snapshot' => $exchangeRateSnapshot,
                'created_by' => Auth::id(),
            ]);

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($data['date']);

            // STEP 5: Auto-deduct inventory stock for income transactions (food sales)
            if ($data['income'] > 0) {
                $this->processInventoryUsage($transaction);
            }

            // Log the transaction creation activity
            \App\Models\ActivityLog::log(
                'create',
                "Created transaction: {$transaction->description} (" . 
                ($transaction->income > 0 ? 'Income: ₩' . number_format($transaction->income, 0) : 'Expense: ₩' . number_format($transaction->expense, 0)) . ")",
                'transactions',
                [
                    'transaction_id' => $transaction->id,
                    'date' => $transaction->date,
                    'amount' => $transaction->income > 0 ? $transaction->income : $transaction->expense,
                    'type' => $transaction->income > 0 ? 'income' : 'expense',
                ]
            );

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing transaction.
     *
     * @param DailyTransaction $transaction
     * @param array $data
     * @return DailyTransaction
     * @throws Exception
     */
    public function updateTransaction(DailyTransaction $transaction, array $data): DailyTransaction
    {
        // Validate that income and expense are not both greater than 0
        if ($data['income'] > 0 && $data['expense'] > 0) {
            throw new Exception('Income and expense cannot both be greater than 0 in one transaction.');
        }

        // Validate that at least one of income or expense is greater than 0
        if ($data['income'] <= 0 && $data['expense'] <= 0) {
            throw new Exception('Either income or expense must be greater than 0.');
        }

        DB::beginTransaction();

        try {
            $oldDate = $transaction->date;
            
            // Get currency (use existing if not changed)
            $currencyId = $data['currency_id'] ?? $transaction->currency_id;
            $currency = Currency::findOrFail($currencyId);
            
            // Determine original amount
            $amountOriginal = $data['income'] > 0 ? $data['income'] : $data['expense'];
            
            // Convert to base currency
            $amountBase = $currency->convertToBase($amountOriginal);
            
            // Store exchange rate snapshot
            $exchangeRateSnapshot = $currency->exchange_rate;
            
            // Calculate income and expense in base currency
            $incomeBase = $data['income'] > 0 ? $amountBase : 0;
            $expenseBase = $data['expense'] > 0 ? $amountBase : 0;

            // Update transaction
            $transaction->update([
                'date' => $data['date'],
                'description' => $data['description'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'category_id' => $data['category_id'],
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $currencyId,
                'amount_original' => $amountOriginal,
                'amount_base' => $amountBase,
                'exchange_rate_snapshot' => $exchangeRateSnapshot,
            ]);

            // Recalculate balance
            $lastBalance = $this->getLastBalance($data['date'], $transaction->id);
            $newBalance = $lastBalance + $incomeBase - $expenseBase;
            $transaction->balance = $newBalance;
            $transaction->save();

            // Update balances for subsequent transactions
            $earliestDate = $oldDate < $data['date'] ? $oldDate : $data['date'];
            $this->updateSubsequentBalances($earliestDate);

            // Log the transaction update activity
            \App\Models\ActivityLog::log(
                'update',
                "Updated transaction: {$transaction->description}",
                'transactions',
                [
                    'transaction_id' => $transaction->id,
                    'date' => $transaction->date,
                    'amount' => $transaction->income > 0 ? $transaction->income : $transaction->expense,
                    'type' => $transaction->income > 0 ? 'income' : 'expense',
                ]
            );

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a transaction.
     *
     * @param DailyTransaction $transaction
     * @return bool
     * @throws Exception
     */
    public function deleteTransaction(DailyTransaction $transaction): bool
    {
        DB::beginTransaction();

        try {
            $date = $transaction->date;
            $description = $transaction->description;
            $amount = $transaction->income > 0 ? $transaction->income : $transaction->expense;
            $type = $transaction->income > 0 ? 'income' : 'expense';
            
            $transaction->delete();

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($date);

            // Log the transaction deletion activity
            \App\Models\ActivityLog::log(
                'delete',
                "Deleted transaction: {$description} ({$type}: ₩" . number_format($amount, 0) . ")",
                'transactions',
                [
                    'date' => $date,
                    'amount' => $amount,
                    'type' => $type,
                ]
            );

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get the last balance before a given date.
     *
     * @param string $date
     * @param int|null $excludeId
     * @return float
     */
    private function getLastBalance($date, $excludeId = null): float
    {
        $query = DailyTransaction::where('date', '<', $date)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $lastTransaction = $query->first();

        return $lastTransaction ? $lastTransaction->balance : 0;
    }

    /**
     * Update balances for all transactions after a given date.
     *
     * @param string $date
     * @return void
     */
    private function updateSubsequentBalances($date): void
    {
        $transactions = DailyTransaction::where('date', '>=', $date)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $currentBalance = $this->getLastBalance($date);

        foreach ($transactions as $transaction) {
            // Use amount_base for balance calculation (always in KRW)
            $incomeBase = $transaction->income > 0 ? $transaction->amount_base : 0;
            $expenseBase = $transaction->expense > 0 ? $transaction->amount_base : 0;
            
            $currentBalance = $currentBalance + $incomeBase - $expenseBase;
            $transaction->balance = $currentBalance;
            $transaction->saveQuietly(); // Save without triggering events
        }
    }



    /**
     * Get summary statistics.
     *
     * @param string $period 'today', 'week', 'month', 'year'
     * @return array
     */
    public function getSummary($period = 'today'): array
    {
        $query = DailyTransaction::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'week':
                $query->thisWeek();
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        // Use amount_base for all calculations (always in KRW)
        $totalIncome = DailyTransaction::where('income', '>', 0)
            ->when($period !== 'all', function($q) use ($query) {
                return $q->whereIn('id', $query->pluck('id'));
            })
            ->sum('amount_base');
            
        $totalExpense = DailyTransaction::where('expense', '>', 0)
            ->when($period !== 'all', function($q) use ($query) {
                return $q->whereIn('id', $query->pluck('id'));
            })
            ->sum('amount_base');
            
        $netAmount = $totalIncome - $totalExpense;
        
        $currentBalance = DailyTransaction::orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first()
            ?->balance ?? 0;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
            'current_balance' => $currentBalance,
        ];
    }

    /**
     * Get summary analysis for daily, weekly, monthly, or yearly periods.
     * 
     * ACCOUNTING-GRADE ANALYSIS RULES:
     * =================================
     * 1. Date filtering FIRST (transaction_date, not created_at)
     * 2. Use ONLY base_amount (KRW) for all calculations
     * 3. Separate Income/Expense (never mix in SQL)
     * 4. Calculate Net AFTER aggregation (Income - Expense)
     * 5. Zero-fill missing periods
     * 6. Sort chronologically
     * 7. Consistent output structure
     *
     * @param string $analysisType 'daily', 'weekly', 'monthly', 'yearly'
     * @param string $dateFrom Start date (Y-m-d)
     * @param string $dateTo End date (Y-m-d)
     * @return array
     */
    public function getSummaryAnalysis(string $analysisType, string $dateFrom, string $dateTo): array
    {
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        // Validate date range
        if ($startDate->gt($endDate)) {
            throw new \InvalidArgumentException('Start date must be before or equal to end date');
        }

        switch ($analysisType) {
            case 'daily':
                return $this->getDailyAnalysis($startDate, $endDate);
            case 'weekly':
                return $this->getWeeklyAnalysis($startDate, $endDate);
            case 'monthly':
                return $this->getMonthlyAnalysis($startDate, $endDate);
            case 'yearly':
                return $this->getYearlyAnalysis($startDate, $endDate);
            default:
                throw new \InvalidArgumentException('Invalid analysis type. Must be: daily, weekly, monthly, or yearly');
        }
    }

    /**
     * Daily Analysis - One row per calendar date.
     */
    private function getDailyAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        // STEP 1: Filter by date range FIRST
        $transactions = DB::table('daily_transactions')
            ->select(
                DB::raw('DATE(date) as period_date'),
                DB::raw('SUM(CASE WHEN income > 0 THEN amount_base ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN expense > 0 THEN amount_base ELSE 0 END) as total_expense')
            )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('period_date')
            ->orderBy('period_date', 'asc')
            ->get()
            ->keyBy('period_date');

        // STEP 2: Zero-fill missing dates
        $result = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            
            if ($transactions->has($dateKey)) {
                $data = $transactions->get($dateKey);
                $income = (float) $data->total_income;
                $expense = (float) $data->total_expense;
            } else {
                $income = 0;
                $expense = 0;
            }

            $result[] = [
                'label' => $currentDate->format('Y-m-d'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $currentDate->addDay();
        }

        return $result;
    }

    /**
     * Weekly Analysis - ISO weeks (Monday-Sunday).
     */
    private function getWeeklyAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        // STEP 1: Filter by date range and group by ISO week
        $transactions = DB::table('daily_transactions')
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('WEEK(date, 1) as week'),
                DB::raw('SUM(CASE WHEN income > 0 THEN amount_base ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN expense > 0 THEN amount_base ELSE 0 END) as total_expense')
            )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get();

        // Convert to keyed collection
        $transactionsByWeek = $transactions->mapWithKeys(function($item) {
            return ["{$item->year}-W{$item->week}" => $item];
        });

        // STEP 2: Zero-fill missing weeks
        $result = [];
        $currentDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        
        while ($currentDate->lte($endDate)) {
            $year = $currentDate->year;
            $week = $currentDate->isoWeek();
            $weekKey = "{$year}-W{$week}";
            
            if ($transactionsByWeek->has($weekKey)) {
                $data = $transactionsByWeek->get($weekKey);
                $income = (float) $data->total_income;
                $expense = (float) $data->total_expense;
            } else {
                $income = 0;
                $expense = 0;
            }

            $result[] = [
                'label' => "Week {$week} ({$year})",
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $currentDate->addWeek();
        }

        return $result;
    }

    /**
     * Monthly Analysis - Calendar months.
     */
    private function getMonthlyAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        // STEP 1: Filter by date range and group by month
        $transactions = DB::table('daily_transactions')
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(CASE WHEN income > 0 THEN amount_base ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN expense > 0 THEN amount_base ELSE 0 END) as total_expense')
            )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Convert to keyed collection
        $transactionsByMonth = $transactions->mapWithKeys(function($item) {
            return ["{$item->year}-{$item->month}" => $item];
        });

        // STEP 2: Zero-fill missing months
        $result = [];
        $currentDate = $startDate->copy()->startOfMonth();
        
        while ($currentDate->lte($endDate)) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $monthKey = "{$year}-{$month}";
            
            if ($transactionsByMonth->has($monthKey)) {
                $data = $transactionsByMonth->get($monthKey);
                $income = (float) $data->total_income;
                $expense = (float) $data->total_expense;
            } else {
                $income = 0;
                $expense = 0;
            }

            $result[] = [
                'label' => $currentDate->format('F Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $currentDate->addMonth();
        }

        return $result;
    }

    /**
     * Yearly Analysis - Calendar years.
     */
    private function getYearlyAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        // STEP 1: Filter by date range and group by year
        $transactions = DB::table('daily_transactions')
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('SUM(CASE WHEN income > 0 THEN amount_base ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN expense > 0 THEN amount_base ELSE 0 END) as total_expense')
            )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->keyBy('year');

        // STEP 2: Zero-fill missing years
        $result = [];
        $currentYear = $startDate->year;
        $endYear = $endDate->year;
        
        while ($currentYear <= $endYear) {
            if ($transactions->has($currentYear)) {
                $data = $transactions->get($currentYear);
                $income = (float) $data->total_income;
                $expense = (float) $data->total_expense;
            } else {
                $income = 0;
                $expense = 0;
            }

            $result[] = [
                'label' => (string) $currentYear,
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $currentYear++;
        }

        return $result;
    }

    /**
     * Process inventory usage when a food sale (income transaction) occurs.
     * Auto-deduct stock based on usage recipes.
     *
     * @param DailyTransaction $transaction
     * @return void
     */
    private function processInventoryUsage(DailyTransaction $transaction): void
    {
        try {
            // Get usage recipes for this category
            $recipes = ItemUsageRecipe::where('category_id', $transaction->category_id)
                ->where('is_active', true)
                ->with('inventoryItem')
                ->get();

            foreach ($recipes as $recipe) {
                $item = $recipe->inventoryItem;
                
                // Check if sufficient stock
                if ($item->current_stock >= $recipe->quantity_per_sale) {
                    // Create stock movement
                    StockMovement::create([
                        'inventory_item_id' => $item->id,
                        'type' => 'usage',
                        'quantity' => $recipe->quantity_per_sale,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $recipe->quantity_per_sale * $item->unit_cost,
                        'balance_after' => $item->current_stock - $recipe->quantity_per_sale,
                        'reference_type' => 'transaction',
                        'reference_id' => $transaction->id,
                        'notes' => "Auto-deducted from sale: {$transaction->description}",
                        'movement_date' => $transaction->date,
                        'created_by' => Auth::id(),
                    ]);

                    // Update item stock
                    $item->updateStock($recipe->quantity_per_sale, 'usage');
                } else {
                    // Log warning but don't fail the transaction
                    Log::warning("Insufficient stock for item {$item->name} (ID: {$item->id}). Required: {$recipe->quantity_per_sale}, Available: {$item->current_stock}");
                }
            }
        } catch (Exception $e) {
            // Log error but don't fail the transaction
            Log::error("Inventory usage processing failed: " . $e->getMessage());
        }
    }
}
