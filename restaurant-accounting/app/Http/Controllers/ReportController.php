<?php

namespace App\Http\Controllers;

use App\Models\DailyTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    /**
     * Display the reports page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Export report as CSV.
     */
    public function exportCsv(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_source' => 'nullable|in:all,normal,inventory',
        ]);

        $query = DailyTransaction::with(['category', 'paymentMethod', 'creator', 'currency'])
            ->dateRange($validated['date_from'], $validated['date_to']);

        // Apply transaction source filter
        $query = $this->applyTransactionSourceFilter($query, $validated['transaction_source'] ?? 'all');

        $transactions = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $activeCurrency = getActiveCurrency();
        $filename = 'transactions_' . $validated['date_from'] . '_to_' . $validated['date_to'] . '_' . $activeCurrency->code . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions, $activeCurrency) {
            $file = fopen('php://output', 'w');

            // Add CSV headers with currency information
            fputcsv($file, ['Currency: ' . $activeCurrency->code . ' (' . $activeCurrency->symbol . ')']);
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Date', 'Description', 'Category', 'Payment Method', 'Income', 'Expense', 'Balance', 'Created By']);

            // Add data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->date->format('Y-m-d'),
                    strip_tags($transaction->description),
                    $transaction->category->name,
                    $transaction->paymentMethod->name,
                    $transaction->income > 0 ? number_format(convertCurrency($transaction->income, $activeCurrency), 2) : '0.00',
                    $transaction->expense > 0 ? number_format(convertCurrency($transaction->expense, $activeCurrency), 2) : '0.00',
                    number_format(convertCurrency($transaction->balance, $activeCurrency), 2),
                    $transaction->creator->name,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_source' => 'nullable|in:all,normal,inventory',
        ]);

        $query = DailyTransaction::with(['category', 'paymentMethod', 'creator'])
            ->dateRange($validated['date_from'], $validated['date_to']);

        // Apply transaction source filter
        $query = $this->applyTransactionSourceFilter($query, $validated['transaction_source'] ?? 'all');

        $transactions = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalIncome = $transactions->sum('income');
        $totalExpense = $transactions->sum('expense');
        $netAmount = $totalIncome - $totalExpense;

        // Get timezone from authenticated user or use app default
        $timezone = config('app.timezone');
        $generatedAt = Carbon::now($timezone);

        $data = [
            'transactions' => $transactions,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
            'generated_at' => $generatedAt,
        ];

        // For now, return a simple HTML view that can be printed as PDF
        // You can later integrate a PDF library like DomPDF or TCPDF
        return view('reports.pdf', $data);
    }

    /**
     * Export summary report.
     */
    public function exportSummary(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'transaction_source' => 'nullable|in:all,normal,inventory',
        ]);

        $query = DailyTransaction::with(['category'])
            ->dateRange($validated['date_from'], $validated['date_to']);

        // Apply transaction source filter
        $query = $this->applyTransactionSourceFilter($query, $validated['transaction_source'] ?? 'all');

        $transactions = $query->get();

        // Group by category
        $categoryWise = $transactions->groupBy('category.name')->map(function ($group) {
            return [
                'total_income' => $group->sum('income'),
                'total_expense' => $group->sum('expense'),
                'count' => $group->count(),
            ];
        });

        // Group by payment method
        $paymentMethodWise = $transactions->groupBy('paymentMethod.name')->map(function ($group) {
            return [
                'total_income' => $group->sum('income'),
                'total_expense' => $group->sum('expense'),
                'count' => $group->count(),
            ];
        });

        // Get timezone from authenticated user or use app default
        $timezone = config('app.timezone');
        $generatedAt = Carbon::now($timezone);

        $data = [
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'period' => $validated['period'],
            'total_income' => $transactions->sum('income'),
            'total_expense' => $transactions->sum('expense'),
            'category_wise' => $categoryWise,
            'payment_method_wise' => $paymentMethodWise,
            'generated_at' => $generatedAt,
        ];

        return view('reports.summary', $data);
    }

    /**
     * Apply transaction source filter to query.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $source
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyTransactionSourceFilter($query, $source)
    {
        if ($source === 'inventory') {
            // Only Inventory Transactions (both income and expense)
            $query->whereHas('category', function($q) {
                $q->whereIn('name', ['Inventory Item Sale', 'Inventory Purchase']);
            });
        } elseif ($source === 'normal') {
            // Exclude all Inventory Transactions
            $query->whereHas('category', function($q) {
                $q->whereNotIn('name', ['Inventory Item Sale', 'Inventory Purchase']);
            });
        }
        // 'all' - no filter applied
        
        return $query;
    }

    /**
     * Get summary analysis (Daily, Weekly, Monthly, Yearly).
     * 
     * Returns JSON data for charts and reports.
     * All calculations are based on KRW (base_amount).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummaryAnalysis(Request $request)
    {
        $validated = $request->validate([
            'analysis_type' => 'required|in:daily,weekly,monthly,yearly',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        try {
            $analysisData = $this->transactionService->getSummaryAnalysis(
                $validated['analysis_type'],
                $validated['date_from'],
                $validated['date_to']
            );

            return response()->json([
                'success' => true,
                'analysis_type' => $validated['analysis_type'],
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'data' => $analysisData,
                'currency' => 'KRW', // All calculations are in KRW
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the analysis.',
            ], 500);
        }
    }
}
