<?php

namespace App\Http\Controllers;

use App\Models\DailyTransaction;
use App\Models\PaymentMethod;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantDashboardController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the restaurant accountant dashboard.
     */
    public function index(Request $request)
    {
        $todayIncome = $this->getRestaurantTotalForPeriod('today', 'income');
        $todayExpense = $this->getRestaurantTotalForPeriod('today', 'expense');
        $todayNet = $todayIncome - $todayExpense;
        $currentBalance = $this->getRestaurantCurrentBalance();

        $todaySummary = $this->getRestaurantSummary('today');
        $weekSummary = $this->getRestaurantSummary('week');
        $monthSummary = $this->getRestaurantSummary('month');
        $yearSummary = $this->getRestaurantSummary('year');

        $todayProfit = $this->getRestaurantProfitSummary('today');
        $weekProfit = $this->getRestaurantProfitSummary('week');
        $monthProfit = $this->getRestaurantProfitSummary('month');
        $yearProfit = $this->getRestaurantProfitSummary('year');

        $normalTransactions = $this->restaurantTransactionsQuery()
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

        $weeklyChartData = $this->getWeeklyChartData();
        $monthlyChartData = $this->getMonthlyChartData();

        $categoryIncomes = $this->getGroupedIncomeExpenseData('income');
        $categoryExpenses = $this->getGroupedIncomeExpenseData('expense');

        [$fromDate, $toDate, $transactionType] = $this->normalizePaymentMethodFilters($request);
        $paymentMethodTotals = $this->getPaymentMethodTotals($fromDate, $toDate, $transactionType);

        return view('dashboard.restaurant', compact(
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
            'categoryIncomes',
            'categoryExpenses',
            'paymentMethodTotals',
            'fromDate',
            'toDate',
            'transactionType'
        ));
    }

    /**
     * Base query for restaurant-related transactions.
     */
    private function restaurantTransactionsQuery(): Builder
    {
        return DailyTransaction::query()
            ->where(function (Builder $query) {
                $query->where('source', 'restaurant')
                    ->orWhere(function (Builder $innerQuery) {
                        $innerQuery->where('internal_reference_type', 'inventory_internal_consumption')
                            ->where('expense', '>', 0);
                    });
            });
    }

    /**
     * Get totals for income or expense for a period.
     */
    private function getRestaurantTotalForPeriod(string $period, string $type): float
    {
        $query = $this->applyPeriodFilter($this->restaurantTransactionsQuery(), $period);

        $column = $type === 'income' ? 'income' : 'expense';

        return (float) $query->where($column, '>', 0)->sum($column);
    }

    /**
     * Get current balance for restaurant transactions.
     */
    private function getRestaurantCurrentBalance(): float
    {
        return (float) $this->restaurantTransactionsQuery()
            ->sum(DB::raw('COALESCE(income, 0) - COALESCE(expense, 0)'));
    }

    /**
     * Get summary totals for a period.
     */
    private function getRestaurantSummary(string $period): array
    {
        $query = $this->applyPeriodFilter($this->restaurantTransactionsQuery(), $period);

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
     * Get profit breakdown for restaurant-only data.
     */
    private function getRestaurantProfitSummary(string $period): array
    {
        $query = $this->applyPeriodFilter($this->restaurantTransactionsQuery(), $period);

        $normalIncome = (float) (clone $query)->where('income', '>', 0)->sum('income');
        $normalExpense = (float) (clone $query)->where('expense', '>', 0)->sum('expense');
        $normalProfit = $normalIncome - $normalExpense;

        $inventorySaleRevenue = 0.0;
        $inventoryCOGS = 0.0;
        $inventoryProfit = 0.0;
        $totalProfit = $normalProfit + $inventoryProfit;

        return [
            'normal_income' => $normalIncome,
            'normal_expense' => $normalExpense,
            'normal_profit' => $normalProfit,
            'inventory_sale_revenue' => $inventorySaleRevenue,
            'inventory_cogs' => $inventoryCOGS,
            'inventory_profit' => $inventoryProfit,
            'total_profit' => $totalProfit,
        ];
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

        $totalsSubquery = $this->restaurantTransactionsQuery()
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

            $dayIncome = (float) $this->restaurantTransactionsQuery()
                ->whereDate('date', $date)
                ->sum('income');

            $dayExpense = (float) $this->restaurantTransactionsQuery()
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

            $dayIncome = (float) $this->restaurantTransactionsQuery()
                ->whereDate('date', $currentDate)
                ->sum('income');

            $dayExpense = (float) $this->restaurantTransactionsQuery()
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

        $transactions = $this->restaurantTransactionsQuery()
            ->with(['category'])
            ->thisMonth()
            ->where($amountColumn, '>', 0)
            ->get();

        $grouped = [];

        foreach ($transactions as $transaction) {
            $stockMovement = \App\Models\StockMovement::where('reference_type', 'App\\Models\\DailyTransaction')
                ->where('reference_id', $transaction->id)
                ->with('inventoryItem')
                ->first();

            $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';
            $itemName = null;

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
