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
        
        // PRODUCTION-SAFE: Validate and sanitize transaction source
        $transactionSource = $this->validateTransactionSource($request->input('transaction_source'));

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

        // Calculate profit metrics
        $profitSummary = $this->transactionService->getProfitSummaryByDateRange($dateFrom, $dateTo);

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
            'profitSummary' => $profitSummary,
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

        // PRODUCTION-SAFE: Validate transaction source
        $transactionSource = $this->validateTransactionSource($validated['transaction_source'] ?? null);

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
        );

        // Calculate profit based on transaction source
        $profit = $this->calculateProfitBySource(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
        );

        // Calculate totals for summary
        // For inventory reports, exclude damage from expense/net calculations
        if ($transactionSource === 'inventory') {
            $regularTransactions = $transactions->filter(function ($item) {
                return !isset($item->is_damage_entry) || $item->is_damage_entry !== true;
            });
            
            $totalIncome = $regularTransactions->sum('income');
            $totalExpense = $regularTransactions->sum('expense');
        } else {
            $totalIncome = $transactions->sum('income');
            $totalExpense = $transactions->sum('expense');
        }
        
        $netAmount = $totalIncome - $totalExpense;

        $activeCurrency = getActiveCurrency();
        $filename = 'transactions_' . $validated['date_from'] . '_to_' . $validated['date_to'] . '_' . $activeCurrency->code . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions, $activeCurrency, $totalIncome, $totalExpense, $netAmount, $profit) {
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

            // Add summary section
            fputcsv($file, []); // Empty row
            fputcsv($file, []); // Empty row
            fputcsv($file, ['FINANCIAL SUMMARY']);
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Total Income', number_format(convertCurrency($totalIncome, $activeCurrency), 2)]);
            fputcsv($file, ['Total Expense', number_format(convertCurrency($totalExpense, $activeCurrency), 2)]);
            fputcsv($file, ['Net Amount', number_format(convertCurrency($netAmount, $activeCurrency), 2)]);
            fputcsv($file, ['Profit', number_format(convertCurrency($profit, $activeCurrency), 2)]);

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

        // PRODUCTION-SAFE: Validate transaction source
        $transactionSource = $this->validateTransactionSource($validated['transaction_source'] ?? null);

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
        );

        // Calculate profit based on transaction source
        $profit = $this->calculateProfitBySource(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
        );

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
            'profit' => $profit,
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

        // PRODUCTION-SAFE: Validate transaction source
        $transactionSource = $this->validateTransactionSource($validated['transaction_source'] ?? null);

        // Get transactions based on source filter
        $transactions = $this->getTransactionsForReport(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
        );

        // Calculate profit based on transaction source
        $profit = $this->calculateProfitBySource(
            $validated['date_from'],
            $validated['date_to'],
            $transactionSource
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
            'profit' => $profit,
            'category_wise' => $categoryWise,
            'payment_method_wise' => $paymentMethodWise,
            'total_damage_loss' => $totalDamageLoss,
            'generated_at' => $generatedAt,
        ];

        return view('reports.summary', $data);
    }

    /**
     * Calculate profit based on transaction source and date range.
     * 
     * PROFIT CALCULATION RULES:
     * 1. Normal Transaction Profit: total_normal_income - total_normal_expense
     * 2. Inventory Profit: total_inventory_sales - FIFO_inventory_cost
     * 3. Result depends on transaction_source filter
     * 
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $transactionSource 'all', 'normal', or 'inventory'
     * @return float Calculated profit
     */
    private function calculateProfitBySource(string $dateFrom, string $dateTo, string $transactionSource): float
    {
        // Get full profit breakdown from TransactionService
        $profitSummary = $this->transactionService->getProfitSummaryByDateRange($dateFrom, $dateTo);
        
        // Return profit based on transaction source filter
        switch ($transactionSource) {
            case 'normal':
                // Only normal transaction profit
                return $profitSummary['normal_profit'];
                
            case 'inventory':
                // Only inventory profit
                return $profitSummary['inventory_profit'];
                
            case 'all':
            default:
                // Combined profit (normal + inventory)
                return $profitSummary['total_profit'];
        }
    }

    /**
     * PRODUCTION-SAFE: Validate and sanitize transaction source input.
     * Prevents invalid values from causing empty results or errors.
     * 
     * @param mixed $source
     * @return string Valid source: 'all', 'normal', or 'inventory'
     */
    private function validateTransactionSource($source): string
    {
        // Defensive: handle null, empty, or invalid input
        if (!is_string($source) || !in_array($source, ['all', 'normal', 'inventory'], true)) {
            return 'all'; // Safe default
        }
        
        return $source;
    }

    /**
     * STRICT & PRODUCTION-SAFE: Apply transaction source filter to query.
     * 
     * Uses comprehensive multi-criteria filtering to identify ALL inventory transactions:
     * - Category names (primary identifier for inventory sales, purchases, damage)
     * - InventoryAdjustment relationship (captures purchase corrections)
     * - StockMovement relationship via transaction description (captures inventory item expenses)
     * 
     * MANDATORY FILTERING RULES:
     * - INVENTORY: Include ALL transactions related to inventory in ANY way
     * - NORMAL: Exclude ALL transactions with ANY inventory linkage
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $source Validated source: 'all', 'normal', or 'inventory'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyTransactionSourceFilter($query, string $source)
    {
        if ($source === 'normal') {
            // STRICT: Only Normal Transactions
            // MUST exclude ALL inventory-related transactions using comprehensive criteria
            
            // Get all transaction IDs that have inventory linkages
            $inventoryTransactionIds = $this->getInventoryTransactionIds();
            
            // Exclude transactions by ID (most reliable method)
            if ($inventoryTransactionIds->isNotEmpty()) {
                $query->whereNotIn('id', $inventoryTransactionIds);
            }
            
            // Additional safety: Exclude by category name as secondary filter
            $query->whereHas('category', function($subQ) {
                $subQ->whereNotIn('name', [
                    'Inventory Item Sale',
                    'Inventory Purchase',
                    'Inventory Damage'
                ]);
            });
            
        } elseif ($source === 'inventory') {
            // STRICT: Only Inventory Transactions
            // MUST include ALL inventory-related transactions using comprehensive criteria
            
            // Get all transaction IDs that have inventory linkages
            $inventoryTransactionIds = $this->getInventoryTransactionIds();
            
            // Include transactions by ID (most reliable method)
            if ($inventoryTransactionIds->isNotEmpty()) {
                $query->whereIn('id', $inventoryTransactionIds);
            } else {
                // Fallback: If no inventory transactions found, use category-based filter
                $query->whereHas('category', function($subQ) {
                    $subQ->whereIn('name', [
                        'Inventory Item Sale',
                        'Inventory Purchase',
                        'Inventory Damage'
                    ]);
                });
            }
        }
        
        // 'all' - no filter applied, returns everything
        return $query;
    }
    
    /**
     * Get all transaction IDs that are inventory-related.
     * 
     * Identifies inventory transactions using ALL possible linkage methods:
     * 1. Category names (Inventory Item Sale, Inventory Purchase, Inventory Damage)
     * 2. InventoryAdjustment linkage (purchase corrections)
     * 3. StockMovement linkage (ALL inventory operations including opening stock)
     * 4. Description pattern matching (opening stock, etc.)
     * 
     * CRITICAL: Includes Opening Stock transactions (accounting requirement)
     * 
     * @return \Illuminate\Support\Collection Collection of transaction IDs
     */
    private function getInventoryTransactionIds()
    {
        $transactionIds = collect();
        
        // Method 1: Get transactions by inventory category names
        // Includes: Inventory Item Sale, Inventory Purchase (including opening stock), Inventory Damage
        $categoryBasedIds = DailyTransaction::whereHas('category', function($q) {
            $q->whereIn('name', [
                'Inventory Item Sale',
                'Inventory Purchase',
                'Inventory Damage'
            ]);
        })->pluck('id');
        
        $transactionIds = $transactionIds->merge($categoryBasedIds);
        
        // Method 2: Get transactions linked via InventoryAdjustment (purchase corrections)
        $adjustmentLinkedIds = \App\Models\InventoryAdjustment::whereNotNull('expense_transaction_id')
            ->pluck('expense_transaction_id');
        
        $transactionIds = $transactionIds->merge($adjustmentLinkedIds);
        
        // Method 3: Get transactions linked via StockMovement (ALL inventory operations)
        // CRITICAL: Captures opening stock, inventory sales, and item expenses
        // Get all possible reference_ids from stock movements that might point to transactions
        $allStockMovementRefIds = \App\Models\StockMovement::whereNotNull('reference_id')
            ->pluck('reference_id')
            ->unique();
        
        // Verify which reference_ids are actual transaction IDs (defensive filtering)
        if ($allStockMovementRefIds->isNotEmpty()) {
            $stockMovementLinkedIds = DailyTransaction::whereIn('id', $allStockMovementRefIds)
                ->pluck('id');
            
            $transactionIds = $transactionIds->merge($stockMovementLinkedIds);
        }
        
        // Method 4: Get transactions by description pattern (opening stock, inventory operations)
        // CRITICAL: Ensures opening stock transactions are ALWAYS captured
        $descriptionBasedIds = DailyTransaction::where(function($q) {
            $q->where('description', 'LIKE', 'Opening Stock:%')
              ->orWhere('description', 'LIKE', 'Inventory%')
              ->orWhere('description', 'LIKE', '%Opening Stock Correction%');
        })->pluck('id');
        
        $transactionIds = $transactionIds->merge($descriptionBasedIds);
        
        // Return unique transaction IDs
        return $transactionIds->unique();
    }

    /**
     * UNIFIED METHOD: Get transactions for all report types.
     * This is the single source of truth for transaction queries.
     * 
     * PRODUCTION-SAFE: Validated source parameter ensures consistent filtering.
     * Filters by source (via category + inventory adjustments) and date range.
     * Used by: index (view), exportCsv, exportPdf, exportSummary
     *
     * @param string $dateFrom Date in Y-m-d format
     * @param string $dateTo Date in Y-m-d format
     * @param string $source Validated source: 'all', 'normal', or 'inventory'
     * @return \Illuminate\Support\Collection
     */
    private function getTransactionsForReport($dateFrom, $dateTo, $source)
    {
        // Base query using Transaction (alias for DailyTransaction)
        $query = Transaction::query()
            ->with(['category', 'paymentMethod', 'creator', 'currency', 'inventoryAdjustments']);
        
        // CRITICAL: Apply source filter FIRST (before date) for efficiency
        // This method handles production-safe filtering with multiple criteria
        $query = $this->applyTransactionSourceFilter($query, $source);
        
        // Apply date range filter (using 'date' column as transaction_date equivalent)
        $query->whereBetween('date', [$dateFrom, $dateTo]);
        
        // Execute query and get transactions
        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        // If inventory source, include damage/spoilage from stock_movements
        // PRODUCTION-SAFE: Only fetch damage entries when specifically filtering for inventory
        if ($source === 'inventory') {
            $damageEntries = $this->getDamageEntriesForReport($dateFrom, $dateTo);
            
            // Convert to plain collection and merge (defensive against empty collections)
            $transactions = collect($transactions->all())->merge($damageEntries);
            
            // Re-sort the merged collection by date (desc) then ID (desc)
            $transactions = $transactions->sortByDesc(function ($item) {
                // Handle both Carbon date objects and string dates
                $date = is_object($item->date) ? $item->date->format('Y-m-d H:i:s') : $item->date;
                // Handle pseudo IDs (like 'damage-123') vs numeric IDs
                $id = is_string($item->id) ? 0 : ($item->id ?? 0);
                return $date . '-' . str_pad($id, 10, '0', STR_PAD_LEFT);
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
     * PRODUCTION-SAFE: Handles missing relationships and null values gracefully.
     *
     * @param string $dateFrom Date in Y-m-d format
     * @param string $dateTo Date in Y-m-d format
     * @return \Illuminate\Support\Collection
     */
    private function getDamageEntriesForReport($dateFrom, $dateTo)
    {
        try {
            $damageMovements = \App\Models\StockMovement::query()
                ->with(['inventoryItem', 'creator'])
                ->where('type', 'adjustment')
                ->where('reference_type', 'damage_spoilage')
                ->whereBetween('movement_date', [$dateFrom, $dateTo])
                ->orderBy('movement_date', 'desc')
                ->get();
        } catch (\Exception $e) {
            // PRODUCTION-SAFE: Return empty collection on query failure
            \Illuminate\Support\Facades\Log::error('Failed to fetch damage entries for report', [
                'error' => $e->getMessage(),
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]);
            return collect([]);
        }
        
        // Convert stock movements to transaction-like objects
        return $damageMovements->map(function ($movement) {
            // DEFENSIVE: Handle missing inventory item
            if (!$movement->inventoryItem) {
                return null;
            }
            
            // Calculate the damage cost (absolute value, with fallback)
            $damageExpense = abs(($movement->quantity ?? 0) * ($movement->unit_cost ?? 0));
            
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
            
            // Creator from stock movement (with fallback)
            $pseudo->creator = $movement->creator ?? null;
            
            // Currency - inventory is always in base currency (KRW)
            $pseudo->currency = null;
            
            // Mark as damage entry for special handling
            $pseudo->is_damage_entry = true;
            $pseudo->stock_movement_id = $movement->id;
            
            return $pseudo;
        })->filter(); // PRODUCTION-SAFE: Remove null entries from failed mappings
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
