<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Currency;
use App\Models\DailyTransaction;
use App\Models\PaymentMethod;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = DailyTransaction::with(['category', 'paymentMethod', 'currency', 'creator']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('type')) {
            if ($request->type === 'income') {
                $query->income();
            } elseif ($request->type === 'expense') {
                $query->expense();
            }
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Order by date and id
        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // Enrich purchase correction descriptions for transaction list display
        foreach ($transactions as $transaction) {
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
                    // Build short, clean description format for transaction list
                    $itemName = $adjustment->inventoryItem->name;
                    $unit = $adjustment->inventoryItem->unit ?? 'unit';
                    
                    // Use the stored old/new quantities from the LATEST adjustment record
                    $oldQty = rtrim(rtrim(number_format($adjustment->old_quantity, 2), '0'), '.');
                    $newQty = rtrim(rtrim(number_format($adjustment->new_quantity, 2), '0'), '.');
                    
                    // Short format for transactions list: "Stock Correction – Item (old → new unit)"
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
        
        // Preserve filter parameters in pagination
        $transactions->appends($request->query());

        // Get filter options (load dynamically from database)
        $categories = Category::all();
        $paymentMethods = PaymentMethod::active()->get();
        
        // Get active currency for display
        $activeCurrency = getActiveCurrency();

        return view('transactions.index', compact('transactions', 'categories', 'paymentMethods', 'activeCurrency'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        // Fetch all categories with id, name, and type for client-side filtering
        $categories = Category::select('id', 'name', 'type')
            ->orderBy('name')
            ->get();
        
        $paymentMethods = PaymentMethod::active()->get();
        $currencies = Currency::getActive();
        $defaultCurrency = Currency::getDefault();

        return view('transactions.create', compact(
            'categories',
            'paymentMethods',
            'currencies',
            'defaultCurrency'
        ));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:5000',
            'transaction_type' => 'required|in:income,expense',
            'income' => 'nullable|numeric|min:0',
            'expense' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);
        
        // Validate that category type matches transaction type
        $category = Category::findOrFail($validated['category_id']);
        if ($category->type !== $validated['transaction_type']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category_id' => 'Selected category does not match the transaction type.']);
        }

        // Set defaults
        $validated['income'] = $validated['income'] ?? 0;
        $validated['expense'] = $validated['expense'] ?? 0;
        
        // Sanitize HTML description to prevent XSS
        $validated['description'] = $this->sanitizeHtml($validated['description']);

        try {
            $this->transactionService->createTransaction($validated);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(DailyTransaction $transaction)
    {
        // Load relationships
        $transaction->load(['category', 'paymentMethod', 'currency', 'creator']);
        
        // Get active currency for display
        $activeCurrency = getActiveCurrency();

        return view('transactions.show', compact('transaction', 'activeCurrency'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(DailyTransaction $transaction)
    {
        // Fetch all categories with id, name, and type for client-side filtering
        $categories = Category::select('id', 'name', 'type')
            ->orderBy('name')
            ->get();
        
        $paymentMethods = PaymentMethod::active()->get();
        $currencies = Currency::getActive();

        return view('transactions.edit', compact(
            'transaction',
            'categories',
            'paymentMethods',
            'currencies'
        ));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, DailyTransaction $transaction)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:5000',
            'transaction_type' => 'required|in:income,expense',
            'income' => 'nullable|numeric|min:0',
            'expense' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);
        
        // Determine original transaction type
        $originalType = $transaction->income > 0 ? 'income' : 'expense';
        $newType = $validated['transaction_type'];
        
        // CRITICAL: Transaction type changes are ONLY allowed for NORMAL transactions
        // ALL inventory-related transactions are protected to maintain inventory integrity
        if ($originalType !== $newType) {
            // Check if this is ANY type of inventory transaction
            $isInventoryTransaction = false;
            
            // Method 1: Check if linked to inventory adjustments
            if ($transaction->inventoryAdjustments()->exists()) {
                $isInventoryTransaction = true;
            }
            
            // Method 2: Check if category name indicates inventory transaction
            $inventoryCategories = [
                'Inventory Purchase',
                'Inventory Item Sale',
                'Inventory Damage',
                'Inventory Adjustment'
            ];
            
            if ($transaction->category && in_array($transaction->category->name, $inventoryCategories)) {
                $isInventoryTransaction = true;
            }
            
            // Method 3: Check if description contains inventory indicators (purchase corrections, etc.)
            if (stripos($transaction->description, 'Stock Correction') !== false ||
                stripos($transaction->description, 'Inventory') !== false) {
                $isInventoryTransaction = true;
            }
            
            // Block type change if this is any type of inventory transaction
            if ($isInventoryTransaction) {
                $categoryName = $transaction->category ? $transaction->category->name : 'Unknown';
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'transaction_type' => 'Transaction type change is ONLY allowed for NORMAL transactions. This is an INVENTORY transaction (Category: ' . $categoryName . ') and cannot be changed from ' . strtoupper($originalType) . ' to ' . strtoupper($newType) . '. Changing inventory transaction types would corrupt inventory stock records and financial data.'
                    ]);
            }
        }
        
        // Validate that category type matches transaction type
        $category = Category::findOrFail($validated['category_id']);
        if ($category->type !== $validated['transaction_type']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category_id' => 'Selected category does not match the transaction type.']);
        }

        // Set defaults
        $validated['income'] = $validated['income'] ?? 0;
        $validated['expense'] = $validated['expense'] ?? 0;
        
        // Sanitize HTML description to prevent XSS
        $validated['description'] = $this->sanitizeHtml($validated['description']);

        try {
            $this->transactionService->updateTransaction($transaction, $validated);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(DailyTransaction $transaction)
    {
        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Sanitize HTML content to prevent XSS attacks.
     * Allows only safe HTML tags generated by Quill editor.
     */
    private function sanitizeHtml($html)
    {
        // Allow only safe tags from Quill editor
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><a>';
        
        // Strip dangerous tags and attributes
        $cleaned = strip_tags($html, $allowedTags);
        
        // Remove any javascript: or data: protocols from links
        $cleaned = preg_replace('/(<a[^>]+href=[\"\'])(javascript:|data:)/i', '$1#', $cleaned);
        
        // Remove on* event attributes
        $cleaned = preg_replace('/<([^>]+)\s+on\w+=[\""][^\"]*[\""]([^>]*)>/i', '<$1$2>', $cleaned);
        
        return $cleaned;
    }

    /**
     * Show the form for creating an inventory item sale transaction.
     */
    public function createInventorySale()
    {
        // Get all active inventory items with stock
        $inventoryItems = InventoryItem::active()
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();
        
        $paymentMethods = PaymentMethod::active()->get();
        $defaultCurrency = Currency::getDefault();

        // Get or create "Inventory Item Sale" category
        $category = Category::firstOrCreate(
            ['name' => 'Inventory Item Sale', 'type' => 'income'],
            ['description' => 'Sales of inventory items']
        );

        return view('transactions.inventory-sale', compact(
            'inventoryItems',
            'paymentMethods',
            'defaultCurrency',
            'category'
        ));
    }

    /**
     * Store an inventory item sale transaction.
     * This method:
     * 1. Validates the sale (quantity, price, stock availability)
     * 2. Reduces inventory stock
     * 3. Creates a stock movement record
     * 4. Creates an income transaction automatically
     * 5. Updates dashboard values
     */
    public function storeInventorySale(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string|max:5000',
        ]);

        // Get inventory item
        $inventoryItem = InventoryItem::findOrFail($validated['inventory_item_id']);

        // Get selling price from inventory item (CRITICAL: Never trust frontend)
        $sellingPrice = (float) $inventoryItem->selling_price_per_unit;

        // Validate that selling price is set
        if ($sellingPrice <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This inventory item does not have a valid selling price set. Please update the inventory item master first.');
        }

        // Validate stock availability (CRITICAL)
        if ($validated['quantity'] > $inventoryItem->current_stock) {
            return redirect()->back()
                ->withInput()
                ->with('error', sprintf(
                    'Insufficient stock! Available: %s %s, Requested: %s %s',
                    number_format((float)$inventoryItem->current_stock, 2),
                    $inventoryItem->unit,
                    number_format($validated['quantity'], 2),
                    $inventoryItem->unit
                ));
        }

        DB::beginTransaction();

        try {
            $quantity = $validated['quantity'];
            $totalSaleAmount = $quantity * $sellingPrice;

            // Get or create "Inventory Item Sale" category
            $category = Category::firstOrCreate(
                ['name' => 'Inventory Item Sale', 'type' => 'income'],
                ['description' => 'Sales of inventory items']
            );

            // STEP 1: Create the income transaction
            $description = $validated['description'] ?? sprintf(
                'Sale of %s %s of %s @ ₩%s per %s',
                number_format($quantity, 2),
                $inventoryItem->unit,
                $inventoryItem->name,
                number_format($sellingPrice, 2),
                $inventoryItem->unit
            );

            $transaction = $this->transactionService->createTransaction([
                'date' => $validated['date'],
                'description' => $this->sanitizeHtml($description),
                'income' => $totalSaleAmount,
                'expense' => 0,
                'category_id' => $category->id,
                'payment_method_id' => $validated['payment_method_id'],
                'currency_id' => Currency::getDefault()->id,
            ]);

            // STEP 2: Update inventory stock
            $oldStock = $inventoryItem->current_stock;
            $inventoryItem->current_stock -= $quantity;
            $inventoryItem->save();

            // STEP 3: Create stock movement record
            StockMovement::create([
                'inventory_item_id' => $inventoryItem->id,
                'type' => 'sale', // New type for inventory sales
                'quantity' => $quantity,
                'unit_cost' => $inventoryItem->unit_cost,
                'total_cost' => $quantity * $inventoryItem->unit_cost,
                'balance_after' => $inventoryItem->current_stock,
                'reference_type' => DailyTransaction::class,
                'reference_id' => $transaction->id,
                'notes' => sprintf(
                    'Sold via transaction #%d at ₩%s per %s',
                    $transaction->id,
                    number_format($sellingPrice, 2),
                    $inventoryItem->unit
                ),
                'movement_date' => $validated['date'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', sprintf(
                    'Inventory sale recorded successfully! Stock reduced from %s to %s %s',
                    number_format((float)$oldStock, 2),
                    number_format((float)$inventoryItem->current_stock, 2),
                    $inventoryItem->unit
                ));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to record inventory sale: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a multi-item inventory sale transaction.
     */
    public function createInventorySaleMulti()
    {
        // Get all active inventory items with stock
        $inventoryItems = InventoryItem::active()
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();
        
        $paymentMethods = PaymentMethod::active()->get();
        $defaultCurrency = Currency::getDefault();

        // Get or create "Inventory Item Sale" category
        $category = Category::firstOrCreate(
            ['name' => 'Inventory Item Sale', 'type' => 'income'],
            ['description' => 'Sales of inventory items']
        );

        return view('transactions.inventory-sale-multi', compact(
            'inventoryItems',
            'paymentMethods',
            'defaultCurrency',
            'category'
        ));
    }

    /**
     * Store a multi-item inventory sale transaction.
     * Each item creates its own transaction record, but all share a batch_id.
     */
    public function storeInventorySaleMulti(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string|max:5000',
        ]);

        DB::beginTransaction();

        try {
            // Generate unique batch ID for all items in this multi-item transaction
            $batchId = 'BATCH-' . $validated['date'] . '-' . uniqid();
            $grandTotal = 0;
            $processedItems = [];

            // Get or create "Inventory Item Sale" category
            $category = Category::firstOrCreate(
                ['name' => 'Inventory Item Sale', 'type' => 'income'],
                ['description' => 'Sales of inventory items']
            );

            foreach ($validated['items'] as $itemData) {
                // Get inventory item
                $inventoryItem = InventoryItem::findOrFail($itemData['inventory_item_id']);

                // Get selling price from inventory item (CRITICAL: Never trust frontend)
                $sellingPrice = (float) $inventoryItem->selling_price_per_unit;

                // Validate that selling price is set
                if ($sellingPrice <= 0) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Item '{$inventoryItem->name}' does not have a valid selling price set. Please update the inventory item master first.");
                }

                // Validate stock availability (CRITICAL)
                if ($itemData['quantity'] > $inventoryItem->current_stock) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', sprintf(
                            'Insufficient stock for %s! Available: %s %s, Requested: %s %s',
                            $inventoryItem->name,
                            number_format((float)$inventoryItem->current_stock, 2),
                            $inventoryItem->unit,
                            number_format($itemData['quantity'], 2),
                            $inventoryItem->unit
                        ));
                }

                $quantity = $itemData['quantity'];
                $totalSaleAmount = $quantity * $sellingPrice;

                // Create the income transaction for this item
                $description = $validated['description'] ?? sprintf(
                    'Sale of %s %s of %s @ ₩%s per %s',
                    number_format($quantity, 2),
                    $inventoryItem->unit,
                    $inventoryItem->name,
                    number_format($sellingPrice, 2),
                    $inventoryItem->unit
                );

                $transaction = $this->transactionService->createTransaction([
                    'date' => $validated['date'],
                    'description' => $this->sanitizeHtml($description),
                    'income' => $totalSaleAmount,
                    'expense' => 0,
                    'category_id' => $category->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'currency_id' => Currency::getDefault()->id,
                    'batch_id' => $batchId,
                ]);

                // Update inventory stock
                $oldStock = $inventoryItem->current_stock;
                $inventoryItem->current_stock -= $quantity;
                $inventoryItem->save();

                // Create stock movement record
                StockMovement::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'type' => 'sale',
                    'quantity' => $quantity,
                    'unit_cost' => $inventoryItem->unit_cost,
                    'total_cost' => $quantity * $inventoryItem->unit_cost,
                    'balance_after' => $inventoryItem->current_stock,
                    'reference_type' => DailyTransaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => sprintf(
                        'Multi-item sale via transaction #%d at ₩%s per %s',
                        $transaction->id,
                        number_format($sellingPrice, 2),
                        $inventoryItem->unit
                    ),
                    'movement_date' => $validated['date'],
                    'created_by' => Auth::id(),
                ]);

                $grandTotal += $totalSaleAmount;
                $processedItems[] = "{$inventoryItem->name} ({$quantity} {$inventoryItem->unit})";
            }

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', sprintf(
                    'Multi-item inventory sale recorded successfully! %d items processed. Total: ₩%s',
                    count($validated['items']),
                    number_format($grandTotal, 0)
                ));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to record multi-item inventory sale: ' . $e->getMessage());
        }
    }
}