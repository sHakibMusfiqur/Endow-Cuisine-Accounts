<?php

namespace App\Http\Controllers;

use App\Models\DailyTransaction;
use App\Models\InventoryAdjustment;
use App\Models\PaymentMethod;
use App\Models\StockMovement;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the inventory accountant dashboard.
     */
    public function index(Request $request)
    {
        $todayIncome = $this->getInventoryTotalForPeriod('today', 'income');
        $todayExpense = $this->getInventoryTotalForPeriod('today', 'expense');
        $todayNet = $todayIncome - $todayExpense;
        $currentBalance = $this->getInventoryCurrentBalance();

        $todaySummary = $this->getInventorySummary('today');
        $weekSummary = $this->getInventorySummary('week');
        $monthSummary = $this->getInventorySummary('month');
        $yearSummary = $this->getInventorySummary('year');

        $todayProfit = $this->getInventoryProfitSummary('today');
        $weekProfit = $this->getInventoryProfitSummary('week');
        $monthProfit = $this->getInventoryProfitSummary('month');
        $yearProfit = $this->getInventoryProfitSummary('year');

        $normalTransactions = $this->inventoryTransactionsQuery()
            ->with(['category', 'paymentMethod', 'creator'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $damageTransactions = $this->transactionService->getDamageTransactionFeed([], 50);

        $recentTransactions = $this->transactionService->mergeTransactionFeed($normalTransactions, $damageTransactions)
            ->take(10)
            ->values();

        foreach ($recentTransactions as $transaction) {
            if (is_object($transaction->category) && $transaction->category->name === 'Inventory Purchase') {
                $adjustment = \App\Models\InventoryAdjustment::where('expense_transaction_id', $transaction->id)
                    ->where('correction_type', 'purchase_correction')
                    ->with('inventoryItem')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($adjustment && $adjustment->inventoryItem) {
                    $itemName = $adjustment->inventoryItem->name;
                    $unit = $adjustment->inventoryItem->unit ?? 'unit';
                    $oldQty = rtrim(rtrim(number_format($adjustment->old_quantity, 2), '0'), '.');
                    $newQty = rtrim(rtrim(number_format($adjustment->new_quantity, 2), '0'), '.');

                    $transaction->description = sprintf(
                        'Stock Correction - %s (%s -> %s %s)',
                        $itemName,
                        $oldQty,
                        $newQty,
                        $unit
                    );
                }
            }
        }

        $todayDamage = $this->getDamageTotalForPeriod('today');
        $weekDamage = $this->getDamageTotalForPeriod('week');
        $monthDamage = $this->getDamageTotalForPeriod('month');
        $yearDamage = $this->getDamageTotalForPeriod('year');

        $weeklyChartData = $this->getWeeklyChartData();
        $monthlyChartData = $this->getMonthlyChartData();

        $categoryIncomes = $this->getGroupedIncomeExpenseData('income');
        $categoryExpenses = $this->getGroupedIncomeExpenseData('expense');

        [$fromDate, $toDate, $transactionType] = $this->normalizePaymentMethodFilters($request);
        $paymentMethodTotals = $this->getPaymentMethodTotals($fromDate, $toDate, $transactionType);

        return view('dashboard.inventory', compact(
            'todayIncome',
            'todayExpense',
            'todayNet',
            'currentBalance',
            'todaySummary',
            'weekSummary',
            'monthSummary',
            'yearSummary',
            'todayProfit',
            'weekProfit',
            'monthProfit',
            'yearProfit',
            'recentTransactions',
            'weeklyChartData',
            'monthlyChartData',
            'todayDamage',
            'weekDamage',
            'monthDamage',
            'yearDamage',
            'categoryIncomes',
            'categoryExpenses',
            'paymentMethodTotals',
            'fromDate',
            'toDate',
            'transactionType'
        ));
    }

    /**
     * Base query for inventory-related transactions.
     */
    private function inventoryTransactionsQuery(): Builder
    {
        return DailyTransaction::query()
            ->whereHas('category', function (Builder $query) {
                $query->where('module', 'inventory');
            });
    }

    /**
     * Get totals for income or expense for a period.
     */
    private function getInventoryTotalForPeriod(string $period, string $type): float
    {
        $query = $this->applyPeriodFilter($this->inventoryTransactionsQuery(), $period);

        $column = $type === 'income' ? 'income' : 'expense';

        return (float) $query->where($column, '>', 0)->sum($column);
    }

    /**
     * Get current balance for inventory transactions.
     */
    private function getInventoryCurrentBalance(): float
    {
        return (float) $this->inventoryTransactionsQuery()
            ->sum(DB::raw('COALESCE(income, 0) - COALESCE(expense, 0)'));
    }

    /**
     * Get summary totals for a period.
     */
    private function getInventorySummary(string $period): array
    {
        $query = $this->applyPeriodFilter($this->inventoryTransactionsQuery(), $period);

        $totalIncome = (float) (clone $query)->where('income', '>', 0)->sum('income');
        $totalExpense = (float) (clone $query)->where('expense', '>', 0)->sum('expense');
        $netAmount = $totalIncome - $totalExpense;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
        ];
    }

    /**
     * Get profit breakdown using admin dashboard calculation,
     * then force restaurant profit to zero for inventory accountants.
     */
    private function getInventoryProfitSummary(string $period): array
    {
        $profitSummary = $this->transactionService->getProfitSummary($period);

        $profitSummary['normal_income'] = 0.0;
        $profitSummary['normal_expense'] = 0.0;
        $profitSummary['normal_profit'] = 0.0;
        $profitSummary['total_profit'] = $profitSummary['inventory_profit'];

        return $profitSummary;
    }

    /**
     * Normalize payment method filter inputs.
     *
     * @return array{0:?string,1:?string,2:string}
     */
    private function normalizePaymentMethodFilters(Request $request): array
    {
        $fromDate = $request->filled('from_date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('from_date'))->toDateString()
            : null;

        $toDate = $request->filled('to_date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('to_date'))->toDateString()
            : null;

        $transactionType = $request->input('transaction_type', 'all');
        if (!in_array($transactionType, ['all', 'income', 'expense'], true)) {
            $transactionType = 'all';
        }

        if ($fromDate && $toDate && Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate, $toDate, $transactionType];
    }

    /**
     * Get totals for each payment method using the selected filters.
     */
    private function getPaymentMethodTotals(?string $fromDate, ?string $toDate, string $transactionType)
    {
        $amountExpression = match ($transactionType) {
            'income' => 'COALESCE(daily_transactions.income, 0)',
            'expense' => 'COALESCE(daily_transactions.expense, 0)',
            default => 'COALESCE(daily_transactions.income, 0) - COALESCE(daily_transactions.expense, 0)',
        };

        $totalsSubquery = $this->inventoryTransactionsQuery()
            ->select('payment_method_id')
            ->selectRaw("SUM({$amountExpression}) as total_amount")
            ->whereNotNull('payment_method_id')
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('date', [$fromDate, $toDate]);
            })
            ->when($fromDate && !$toDate, function ($query) use ($fromDate) {
                $query->whereDate('date', '>=', $fromDate);
            })
            ->when(!$fromDate && $toDate, function ($query) use ($toDate) {
                $query->whereDate('date', '<=', $toDate);
            })
            ->groupBy('payment_method_id');

        return PaymentMethod::query()
            ->leftJoinSub($totalsSubquery, 'payment_method_totals', function ($join) {
                $join->on('payment_methods.id', '=', 'payment_method_totals.payment_method_id');
            })
            ->select('payment_methods.id', 'payment_methods.name')
            ->selectRaw('COALESCE(payment_method_totals.total_amount, 0) as total_amount')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Base query for inventory damage transactions.
     */
    private function damageTransactionsQuery(): Builder
    {
        return InventoryAdjustment::query()->damageSpoilage();
    }

    /**
     * Get total damage for a period.
     */
    private function getDamageTotalForPeriod(string $period): float
    {
        $query = $this->damageTransactionsQuery()
            ->join('inventory_items', 'inventory_adjustments.inventory_item_id', '=', 'inventory_items.id');

        switch ($period) {
            case 'today':
                $query->whereDate('inventory_adjustments.created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('inventory_adjustments.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
            case 'month':
                $query->whereBetween('inventory_adjustments.created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
                break;
            case 'year':
                $query->whereBetween('inventory_adjustments.created_at', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear(),
                ]);
                break;
        }

        return (float) $query->sum(DB::raw('ABS(inventory_adjustments.difference) * COALESCE(inventory_items.unit_cost, 0)'));
    }

    /**
     * Get weekly chart data (last 7 days).
     */
    private function getWeeklyChartData(): array
    {
        $dates = [];
        $income = [];
        $expense = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('M d');

            $dayIncome = (float) $this->inventoryTransactionsQuery()
                ->whereDate('date', $date)
                ->sum('income');

            $dayExpense = (float) $this->inventoryTransactionsQuery()
                ->whereDate('date', $date)
                ->sum('expense');

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
    private function getMonthlyChartData(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $dates = [];
        $income = [];
        $expense = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('M d');

            $dayIncome = (float) $this->inventoryTransactionsQuery()
                ->whereDate('date', $currentDate)
                ->sum('income');

            $dayExpense = (float) $this->inventoryTransactionsQuery()
                ->whereDate('date', $currentDate)
                ->sum('expense');

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
     */
    private function getGroupedIncomeExpenseData(string $type)
    {
        $amountColumn = $type === 'income' ? 'income' : 'expense';

        $transactions = $this->inventoryTransactionsQuery()
            ->with(['category'])
            ->thisMonth()
            ->where($amountColumn, '>', 0)
            ->get();

        if ($transactions->isEmpty()) {
            return collect();
        }

        $movementLookup = StockMovement::where('reference_type', 'App\\Models\\DailyTransaction')
            ->whereIn('reference_id', $transactions->pluck('id'))
            ->with('inventoryItem')
            ->get()
            ->groupBy('reference_id');

        $grouped = [];

        foreach ($transactions as $transaction) {
            $stockMovement = $movementLookup->get($transaction->id)?->first();
            $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';

            if ($stockMovement && $stockMovement->inventoryItem) {
                $itemName = $stockMovement->inventoryItem->name;
                $key = $categoryName . '|' . $itemName;
            } else {
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

        return collect($grouped)->sortByDesc('amount')->values();
    }

    /**
     * Apply period filters to a query.
     */
    private function applyPeriodFilter(Builder $query, string $period): Builder
    {
        switch ($period) {
            case 'today':
                return $query->today();
            case 'week':
                return $query->thisWeek();
            case 'month':
                return $query->thisMonth();
            case 'year':
                return $query->thisYear();
            default:
                return $query;
        }
    }
}
