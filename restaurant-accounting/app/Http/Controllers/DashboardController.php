<?php

namespace App\Http\Controllers;

use App\Models\DailyTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get summary statistics
        $todaySummary = $this->transactionService->getSummary('today');
        $weekSummary = $this->transactionService->getSummary('week');
        $monthSummary = $this->transactionService->getSummary('month');
        $yearSummary = $this->transactionService->getSummary('year');

        // Get recent transactions
        $recentTransactions = DailyTransaction::with(['category', 'paymentMethod', 'creator'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Enrich purchase correction descriptions for dashboard display
        foreach ($recentTransactions as $transaction) {
            // Check if this is a purchase correction transaction
            if (is_object($transaction->category) && 
                $transaction->category->name === 'Inventory Purchase') {
                
                // Look for related inventory adjustment (purchase correction)
                // CRITICAL: Get the LATEST adjustment if multiple corrections exist
                // Multiple corrections create multiple adjustment records for same transaction
                $adjustment = \App\Models\InventoryAdjustment::where('expense_transaction_id', $transaction->id)
                    ->where('correction_type', 'purchase_correction')
                    ->with('inventoryItem')
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($adjustment && $adjustment->inventoryItem) {
                    // Build short, clean description format
                    $itemName = $adjustment->inventoryItem->name;
                    $unit = $adjustment->inventoryItem->unit ?? 'unit';
                    
                    // Use the stored old/new quantities from the LATEST adjustment record
                    $oldQty = rtrim(rtrim(number_format($adjustment->old_quantity, 2), '0'), '.');
                    $newQty = rtrim(rtrim(number_format($adjustment->new_quantity, 2), '0'), '.');
                    
                    // Short format: "Stock Correction – Item (old → new unit)"
                    $transaction->description = sprintf(
                        'Stock Correction – %s (%s → %s %s)',
                        $itemName,
                        $oldQty,
                        $newQty,
                        $unit
                    );
                }
            }
        }

        // Get weekly chart data (last 7 days)
        $weeklyChartData = $this->getWeeklyChartData();

        // Get monthly chart data (current month by day)
        $monthlyChartData = $this->getMonthlyChartData();

        // Get ALL income entries for current month grouped by category and item
        $categoryIncomes = $this->getGroupedIncomeExpenseData('income');

        // Get ALL expense entries for current month grouped by category and item
        $categoryExpenses = $this->getGroupedIncomeExpenseData('expense');

        return view('dashboard.index', compact(
            'todaySummary',
            'weekSummary',
            'monthSummary',
            'yearSummary',
            'recentTransactions',
            'weeklyChartData',
            'monthlyChartData',
            'categoryIncomes',
            'categoryExpenses'
        ));
    }

    /**
     * Get weekly chart data (last 7 days).
     */
    private function getWeeklyChartData()
    {
        $dates = [];
        $income = [];
        $expense = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('M d');

            $dayIncome = DailyTransaction::whereDate('date', $date)->sum('income');
            $dayExpense = DailyTransaction::whereDate('date', $date)->sum('expense');

            $income[] = $dayIncome;
            $expense[] = $dayExpense;
        }

        return [
            'labels' => $dates,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    /**
     * Get monthly chart data (current month by day).
     */
    private function getMonthlyChartData()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $dates = [];
        $income = [];
        $expense = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('M d');

            $dayIncome = DailyTransaction::whereDate('date', $currentDate)->sum('income');
            $dayExpense = DailyTransaction::whereDate('date', $currentDate)->sum('expense');

            $income[] = $dayIncome;
            $expense[] = $dayExpense;

            $currentDate->addDay();
        }

        return [
            'labels' => $dates,
            'income' => $income,
            'expense' => $expense,
        ];
    }

    /**
     * Get grouped income or expense data with category and inventory item details.
     * 
     * @param string $type 'income' or 'expense'
     * @return \Illuminate\Support\Collection
     */
    private function getGroupedIncomeExpenseData(string $type)
    {
        $amountColumn = $type === 'income' ? 'income' : 'expense';
        
        // Get all transactions for current month
        $transactions = DailyTransaction::with(['category'])
            ->thisMonth()
            ->where($amountColumn, '>', 0)
            ->get();

        // Group transactions by category and description to aggregate amounts
        $grouped = [];
        
        foreach ($transactions as $transaction) {
            // Try to find related stock movement to get inventory item details
            $stockMovement = \App\Models\StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                ->where('reference_id', $transaction->id)
                ->with('inventoryItem')
                ->first();

            $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';
            $itemName = null;
            
            if ($stockMovement && $stockMovement->inventoryItem) {
                // Use inventory item name if available
                $itemName = $stockMovement->inventoryItem->name;
                $key = $categoryName . '|' . $itemName;
            } else {
                // Use transaction description - strip HTML tags for dashboard display
                $itemName = strip_tags($transaction->description);
                $key = $categoryName . '|' . $itemName;
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category' => $categoryName,
                    'item' => $itemName,
                    'amount' => 0,
                ];
            }

            $grouped[$key]['amount'] += $transaction->$amountColumn;
        }

        // Convert to collection and sort by amount descending
        return collect($grouped)->sortByDesc('amount')->values();
    }
}
