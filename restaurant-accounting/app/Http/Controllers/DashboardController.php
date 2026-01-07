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

        // Get weekly chart data (last 7 days)
        $weeklyChartData = $this->getWeeklyChartData();

        // Get monthly chart data (current month by day)
        $monthlyChartData = $this->getMonthlyChartData();

        // Get category-wise expense for current month
        $categoryExpenses = DailyTransaction::with('category')
            ->thisMonth()
            ->expense()
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('expense');
            })
            ->sortDesc()
            ->take(5);

        return view('dashboard.index', compact(
            'todaySummary',
            'weekSummary',
            'monthSummary',
            'yearSummary',
            'recentTransactions',
            'weeklyChartData',
            'monthlyChartData',
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
}
