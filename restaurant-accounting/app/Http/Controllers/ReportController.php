<?php

namespace App\Http\Controllers;

use App\Models\DailyTransaction;
use App\Models\DailyTransaction as Transaction;
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
     * Display the reports page with transaction data.
     */
    public function index(Request $request)
    {
        // Set default date range if not provided (both dates default to TODAY)
        $dateFrom = $request->input('date_from', now()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $transactionSource = $request->input('transaction_source', 'all');

        // Get transactions using unified method
        $transactions = $this->getTransactionsForReport(
            $dateFrom,
            $dateTo,
            $transactionSource
        );

        // Calculate totals
        $totalRecords = $transactions->count();
        
        // For inventory reports, exclude damage from expense/net calculations
        if ($transactionSource === 'inventory') {
            // Separate damage entries from regular transactions
            $regularTransactions = $transactions->filter(function ($item) {
                return !isset($item->is_damage_entry) || $item->is_damage_entry !== true;
            });
            
            $damageTransactions = $transactions->filter(function ($item) {
                return isset($item->is_damage_entry) && $item->is_damage_entry === true;
            });
            
            // Calculate totals EXCLUDING damage (accounting-compliant)
            $totalIncome = $regularTransactions->sum('income');
            $totalExpense = $regularTransactions->sum('expense');
            $netAmount = $totalIncome - $totalExpense;
            
            // Calculate total damage loss separately (for reporting only)
            $totalDamageLoss = $damageTransactions->sum('expense');
        } else {
            // For non-inventory reports, include all transactions normally
            $totalIncome = $transactions->sum('income');
            $totalExpense = $transactions->sum('expense');
            $netAmount = $totalIncome - $totalExpense;
            $totalDamageLoss = 0;
        }

        return view('reports.index', [
            'transactions' => $transactions,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'transaction_source' => $transactionSource,
            'total_records' => $totalRecords,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
            'total_damage_loss' => $totalDamageLoss,
        ]);
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

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $validated['transaction_source'] ?? 'all'
        );

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
                // Handle both DailyTransaction and converted StockMovement objects
                $creatorName = $transaction->creator ? $transaction->creator->name : 'N/A';
                
                fputcsv($file, [
                    $transaction->date->format('Y-m-d'),
                    strip_tags($transaction->description),
                    is_object($transaction->category) ? $transaction->category->name : 'N/A',
                    is_object($transaction->paymentMethod) ? $transaction->paymentMethod->name : 'N/A',
                    $transaction->income > 0 ? number_format(convertCurrency($transaction->income, $activeCurrency), 2) : '0.00',
                    $transaction->expense > 0 ? number_format(convertCurrency($transaction->expense, $activeCurrency), 2) : '0.00',
                    number_format(convertCurrency($transaction->balance, $activeCurrency), 2),
                    $creatorName,
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

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $validated['transaction_source'] ?? 'all'
        );

        // For inventory reports, exclude damage from expense/net calculations
        if (($validated['transaction_source'] ?? 'all') === 'inventory') {
            // Separate damage entries from regular transactions
            $regularTransactions = $transactions->filter(function ($item) {
                return !isset($item->is_damage_entry) || $item->is_damage_entry !== true;
            });
            
            $damageTransactions = $transactions->filter(function ($item) {
                return isset($item->is_damage_entry) && $item->is_damage_entry === true;
            });
            
            // Calculate totals EXCLUDING damage (accounting-compliant)
            $totalIncome = $regularTransactions->sum('income');
            $totalExpense = $regularTransactions->sum('expense');
            $netAmount = $totalIncome - $totalExpense;
            
            // Calculate total damage loss separately (for reporting only)
            $totalDamageLoss = $damageTransactions->sum('expense');
        } else {
            // For non-inventory reports, include all transactions normally
            $totalIncome = $transactions->sum('income');
            $totalExpense = $transactions->sum('expense');
            $netAmount = $totalIncome - $totalExpense;
            $totalDamageLoss = 0;
        }

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
            'total_damage_loss' => $totalDamageLoss,
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

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $validated['transaction_source'] ?? 'all'
        );

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
        
        // For inventory reports, exclude damage from expense/net calculations
        if (($validated['transaction_source'] ?? 'all') === 'inventory') {
            // Separate damage entries from regular transactions
            $regularTransactions = $transactions->filter(function ($item) {
                return !isset($item->is_damage_entry) || $item->is_damage_entry !== true;
            });
            
            $damageTransactions = $transactions->filter(function ($item) {
                return isset($item->is_damage_entry) && $item->is_damage_entry === true;
            });
            
            // Calculate totals EXCLUDING damage (accounting-compliant)
            $totalIncome = $regularTransactions->sum('income');
            $totalExpense = $regularTransactions->sum('expense');
            
            // Calculate total damage loss separately (for reporting only)
            $totalDamageLoss = $damageTransactions->sum('expense');
        } else {
            // For non-inventory reports, include all transactions normally
            $totalIncome = $transactions->sum('income');
            $totalExpense = $transactions->sum('expense');
            $totalDamageLoss = 0;
        }

        $data = [
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'period' => $validated['period'],
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'category_wise' => $categoryWise,
            'payment_method_wise' => $paymentMethodWise,
            'total_damage_loss' => $totalDamageLoss,
            'generated_at' => $generatedAt,
        ];

        return view('reports.summary', $data);
    }

    /**
     * Apply transaction source filter to query.
     * Uses category-based filtering since source column doesn't exist.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $source
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyTransactionSourceFilter($query, $source)
    {
        if ($source === 'normal') {
            // Only Normal Transactions (exclude inventory-related categories)
            $query->whereHas('category', function($q) {
                $q->whereNotIn('name', ['Inventory Item Sale', 'Inventory Purchase']);
            });
        } elseif ($source === 'inventory') {
            // Only Inventory Transactions (sales, purchases, damage, spoilage)
            $query->whereHas('category', function($q) {
                $q->whereIn('name', ['Inventory Item Sale', 'Inventory Purchase']);
            });
        }
        // 'all' - no filter applied
        
        return $query;
    }

    /**
     * UNIFIED METHOD: Get transactions for all report types.
     * This is the single source of truth for transaction queries.
     * 
     * Filters by source (via category) and date range.
     * Used by: index (view), exportCsv, exportPdf, exportSummary
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $source
     * @return \Illuminate\Support\Collection
     */
    private function getTransactionsForReport($dateFrom, $dateTo, $source)
    {
        // Base query using Transaction (alias for DailyTransaction)
        $query = Transaction::query()
            ->with(['category', 'paymentMethod', 'creator', 'currency']);
        
        // Apply source filter FIRST (before date)
        $query = $this->applyTransactionSourceFilter($query, $source);
        
        // Apply date range filter (using 'date' column as transaction_date equivalent)
        $query->whereBetween('date', [$dateFrom, $dateTo]);
        
        // Execute query and get transactions
        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        // If inventory source, include damage/spoilage from stock_movements
        if ($source === 'inventory') {
            $damageEntries = $this->getDamageEntriesForReport($dateFrom, $dateTo);
            
            // Convert to plain collection and merge
            $transactions = collect($transactions->all())->merge($damageEntries);
            
            // Re-sort the merged collection
            $transactions = $transactions->sortByDesc(function ($item) {
                $date = is_object($item->date) ? $item->date->format('Y-m-d H:i:s') : $item->date;
                $id = is_string($item->id) ? 0 : ($item->id ?? 0);
                return $date . '-' . $id;
            })->values();
        }
        
        // ALWAYS enrich purchase correction descriptions (for all sources)
        // This ensures corrected quantities are shown in reports
        $transactions = $this->enrichPurchaseCorrectionDescriptions($transactions);
        
        return $transactions;
    }
    
    /**
     * Get damage/spoilage entries from stock_movements table.
     * Converts them to transaction-like objects for report display.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Support\Collection
     */
    private function getDamageEntriesForReport($dateFrom, $dateTo)
    {
        $damageMovements = \App\Models\StockMovement::query()
            ->with(['inventoryItem', 'creator'])
            ->where('type', 'adjustment')
            ->where('reference_type', 'damage_spoilage')
            ->whereBetween('movement_date', [$dateFrom, $dateTo])
            ->orderBy('movement_date', 'desc')
            ->get();
        
        // Convert stock movements to transaction-like objects
        return $damageMovements->map(function ($movement) {
            // Calculate the damage cost (absolute value)
            $damageExpense = abs($movement->quantity * $movement->unit_cost);
            
            // Format description in professional format
            $itemName = $movement->inventoryItem->name ?? 'Unknown Item';
            $quantity = abs($movement->quantity);
            $unit = $movement->inventoryItem->unit ?? 'unit';
            
            $description = sprintf(
                'Inventory Damage – %s - Qty (%s %s)',
                $itemName,
                number_format($quantity, 2),
                $unit
            );
            
            // Append notes if they exist (strip any redundant prefix)
            if ($movement->notes) {
                $cleanNotes = preg_replace('/^(Damage\/Spoilage|damage\/spoilage):\s*/i', '', trim($movement->notes));
                if ($cleanNotes) {
                    $description .= ' - ' . $cleanNotes;
                }
            }
            
            // Create a pseudo-transaction object
            $pseudo = new \stdClass();
            $pseudo->id = 'damage-' . $movement->id; // Unique ID for tracking
            $pseudo->date = $movement->movement_date;
            $pseudo->description = $description;
            $pseudo->income = 0;
            $pseudo->expense = $damageExpense;
            $pseudo->balance = 0; // Balance not applicable for damage entries
            
            // Create pseudo category object
            $pseudo->category = (object) ['name' => 'Inventory Damage', 'type' => 'expense'];
            
            // Payment method N/A for damage
            $pseudo->paymentMethod = (object) ['name' => 'N/A'];
            
            // Creator from stock movement
            $pseudo->creator = $movement->creator;
            
            // Currency - inventory is always in base currency (KRW)
            $pseudo->currency = null;
            
            // Mark as damage entry for special handling
            $pseudo->is_damage_entry = true;
            $pseudo->stock_movement_id = $movement->id;
            
            return $pseudo;
        });
    }
    
    /**
     * Enrich purchase correction transaction descriptions with FROM → TO format.
     * Shows old quantity → new quantity for better audit clarity.
     *
     * @param \Illuminate\Support\Collection $transactions
     * @return \Illuminate\Support\Collection
     */
    private function enrichPurchaseCorrectionDescriptions($transactions)
    {
        return $transactions->map(function ($transaction) {
            // Skip non-transaction objects (like damage entries)
            if (!isset($transaction->id) || is_string($transaction->id)) {
                return $transaction;
            }
            
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
                    // Build enhanced description with FROM → TO format
                    $itemName = $adjustment->inventoryItem->name;
                    $unit = $adjustment->inventoryItem->unit ?? 'unit';
                    
                    // Use the stored old/new quantities from the LATEST adjustment record
                    $oldQty = rtrim(rtrim(number_format($adjustment->old_quantity, 2), '0'), '.');
                    $newQty = rtrim(rtrim(number_format($adjustment->new_quantity, 2), '0'), '.');
                    
                    // Dynamically render corrected description
                    $transaction->description = sprintf(
                        'Opening Stock Correction – %s – Qty (%s %s → %s %s)',
                        $itemName,
                        $oldQty,
                        $unit,
                        $newQty,
                        $unit
                    );
                }
            }
            
            return $transaction;
        });
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
