<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\DailyTransaction;
use App\Models\Category;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['inventoryItem', 'creator']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('inventory_item_id', $request->item_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $items = InventoryItem::active()->orderBy('name')->get();

        return view('inventory.movements.index', compact('movements', 'items'));
    }

    /**
     * Show the form for stock in (purchase).
     */
    public function createStockIn()
    {
        $items = InventoryItem::active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::active()->orderBy('name')->get();
        return view('inventory.movements.stock-in', compact('items', 'paymentMethods'));
    }

    /**
     * Store a stock in movement and create expense transaction.
     */
    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'movement_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);
            $totalCost = $validated['quantity'] * $validated['unit_cost'];

            // Create stock movement
            $movement = StockMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'total_cost' => $totalCost,
                'balance_after' => $item->current_stock + $validated['quantity'],
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Update item stock
            $item->updateStock($validated['quantity'], 'in');

            // Update item unit cost (weighted average)
            if ($item->current_stock > 0) {
                $item->unit_cost = $validated['unit_cost'];
                $item->save();
            }

            // Create expense transaction for the purchase
            $expenseCategory = Category::where('type', 'expense')
                ->where(function($query) {
                    $query->where('name', 'LIKE', '%inventory%')
                          ->orWhere('name', 'LIKE', '%purchase%')
                          ->orWhere('name', 'LIKE', '%food%')
                          ->orWhere('name', 'LIKE', '%supplies%');
                })
                ->first();

            if (!$expenseCategory) {
                // Create default inventory expense category
                $expenseCategory = Category::create([
                    'name' => 'Inventory Purchase',
                    'type' => 'expense',
                ]);
            }

            // Get previous balance
            $previousTransaction = DailyTransaction::orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $previousBalance = $previousTransaction ? $previousTransaction->balance : 0;

            DailyTransaction::create([
                'date' => $validated['movement_date'],
                'description' => "Stock Purchase: {$item->name} ({$validated['quantity']} {$item->unit})",
                'income' => 0,
                'expense' => $totalCost,
                'balance' => $previousBalance - $totalCost,
                'category_id' => $expenseCategory->id,
                'payment_method_id' => $validated['payment_method_id'],
                'currency_id' => 1, // Default to KRW
                'amount_original' => $totalCost,
                'amount_base' => $totalCost,
                'exchange_rate_snapshot' => 1,
                'created_by' => auth()->id(),
            ]);

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($validated['movement_date']);

            // Log the stock-in activity
            \App\Models\ActivityLog::log(
                'restock',
                "Added stock: {$item->name} - {$validated['quantity']} {$item->unit} (Total: ₩" . number_format($totalCost, 0) . ")",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => $validated['quantity'],
                    'unit' => $item->unit,
                    'unit_cost' => $validated['unit_cost'],
                    'total_cost' => $totalCost,
                    'movement_date' => $validated['movement_date'],
                ]
            );

            DB::commit();

            return redirect()->route('inventory.items.index')
                ->with('success', 'Stock added successfully and expense transaction created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for stock out (waste/damage).
     */
    public function createStockOut()
    {
        $items = InventoryItem::active()->where('current_stock', '>', 0)->orderBy('name')->get();
        return view('inventory.movements.stock-out', compact('items'));
    }

    /**
     * Store a stock out movement.
     */
    public function storeStockOut(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'movement_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);

            // Check if sufficient stock
            if ($item->current_stock < $validated['quantity']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient stock. Available: ' . $item->current_stock . ' ' . $item->unit);
            }

            // Create stock movement
            StockMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'unit_cost' => $item->unit_cost,
                'total_cost' => $validated['quantity'] * $item->unit_cost,
                'balance_after' => $item->current_stock - $validated['quantity'],
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Update item stock
            $item->updateStock($validated['quantity'], 'out');

            // Log the stock-out activity
            \App\Models\ActivityLog::log(
                'stock_out',
                "Removed stock: {$item->name} - {$validated['quantity']} {$item->unit}",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => $validated['quantity'],
                    'unit' => $item->unit,
                    'movement_date' => $validated['movement_date'],
                    'notes' => $validated['notes'],
                ]
            );

            DB::commit();

            return redirect()->route('inventory.items.index')
                ->with('success', 'Stock removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for internal purchase/consumption from own inventory.
     */
    public function createInternalPurchase()
    {
        $items = InventoryItem::active()->where('current_stock', '>', 0)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::active()->orderBy('name')->get();
        return view('inventory.movements.internal-purchase', compact('items', 'paymentMethods'));
    }

    /**
     * Store an internal purchase (consume from own inventory with dual-entry accounting).
     * 
     * DUAL-ENTRY ACCOUNTING:
     * Creates TWO linked transactions:
     * 1. INVENTORY INCOME - Reduces inventory value (source='inventory')
     * 2. RESTAURANT EXPENSE - Records operational cost (source='restaurant')
     * 
     * User selects cost basis: unit_cost OR selling_price
     * Both transactions use the SAME amount based on selected pricing
     */
    public function storeInternalPurchase(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'cost_basis' => 'required|in:unit_cost,selling_price',
            'movement_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);

            // Check if sufficient stock
            if ($item->current_stock < $validated['quantity']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient stock. Available: ' . $item->current_stock . ' ' . $item->unit);
            }

            // Determine pricing based on selected cost basis
            $pricePerUnit = $validated['cost_basis'] === 'unit_cost' 
                ? $item->unit_cost 
                : $item->selling_price_per_unit;
            
            $totalAmount = $validated['quantity'] * $pricePerUnit;
            $costBasisLabel = $validated['cost_basis'] === 'unit_cost' 
                ? 'Unit Cost' 
                : 'Selling Price';
            
            // Generate unique reference ID for linking both transactions
            $internalReferenceId = 'IIC-' . $validated['movement_date'] . '-' . uniqid();

            // Create stock movement for internal consumption
            $movement = StockMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'internal_purchase',
                'quantity' => $validated['quantity'],
                'unit_cost' => $item->unit_cost,
                'total_cost' => $item->unit_cost * $validated['quantity'],
                'balance_after' => $item->current_stock - $validated['quantity'],
                'movement_date' => $validated['movement_date'],
                'notes' => ($validated['notes'] ?? 'Internal inventory consumption') . " (Basis: {$costBasisLabel})",
                'created_by' => auth()->id(),
            ]);

            // Update item stock
            $item->updateStock($validated['quantity'], 'out');

            // ═══════════════════════════════════════════════════════════════
            // DUAL-ENTRY TRANSACTION 1: INVENTORY INCOME
            // ═══════════════════════════════════════════════════════════════
            // Purpose: Reduce inventory value (inventory gives out value)
            
            // Find or create income category for inventory
            $inventoryIncomeCategory = Category::where('type', 'income')
                ->where(function($query) {
                    $query->where('name', 'LIKE', '%inventory%')
                          ->orWhere('name', 'LIKE', '%internal%');
                })
                ->first();

            if (!$inventoryIncomeCategory) {
                $inventoryIncomeCategory = Category::create([
                    'name' => 'Inventory Internal Usage',
                    'type' => 'income',
                ]);
            }

            // Get previous balance for calculation
            $previousTransaction = DailyTransaction::orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $previousBalance = $previousTransaction ? $previousTransaction->balance : 0;

            // Create INVENTORY INCOME transaction
            $inventoryTransaction = DailyTransaction::create([
                'date' => $validated['movement_date'],
                'description' => "Inventory internal usage – {$item->name} ({$validated['quantity']} {$item->unit})",
                'source' => 'inventory',
                'income' => $totalAmount,
                'expense' => 0,
                'balance' => $previousBalance + $totalAmount,
                'category_id' => $inventoryIncomeCategory->id,
                'payment_method_id' => $validated['payment_method_id'],
                'currency_id' => 1, // Default to KRW
                'amount_original' => $totalAmount,
                'amount_base' => $totalAmount,
                'exchange_rate_snapshot' => 1,
                'internal_reference_id' => $internalReferenceId,
                'internal_reference_type' => 'inventory_internal_consumption',
                'created_by' => auth()->id(),
            ]);

            // ═══════════════════════════════════════════════════════════════
            // DUAL-ENTRY TRANSACTION 2: RESTAURANT EXPENSE
            // ═══════════════════════════════════════════════════════════════
            // Purpose: Record operational cost (restaurant consumes resources)
            
            // Find or create expense category for restaurant consumption
            $restaurantExpenseCategory = Category::where('type', 'expense')
                ->where(function($query) {
                    $query->where('name', 'LIKE', '%ingredient%')
                          ->orWhere('name', 'LIKE', '%consumption%')
                          ->orWhere('name', 'LIKE', '%inventory%');
                })
                ->first();

            if (!$restaurantExpenseCategory) {
                $restaurantExpenseCategory = Category::create([
                    'name' => 'Ingredients / Inventory Consumption',
                    'type' => 'expense',
                ]);
            }

            // Get updated balance after inventory transaction
            $currentBalance = $inventoryTransaction->balance;

            // Create RESTAURANT EXPENSE transaction
            $restaurantTransaction = DailyTransaction::create([
                'date' => $validated['movement_date'],
                'description' => "Inventory consumed by restaurant – {$item->name}" . 
                                ($validated['notes'] ? " – {$validated['notes']}" : "") . 
                                " (@ {$costBasisLabel})",
                'source' => 'restaurant',
                'income' => 0,
                'expense' => $totalAmount,
                'balance' => $currentBalance - $totalAmount,
                'category_id' => $restaurantExpenseCategory->id,
                'payment_method_id' => $validated['payment_method_id'],
                'currency_id' => 1, // Default to KRW
                'amount_original' => $totalAmount,
                'amount_base' => $totalAmount,
                'exchange_rate_snapshot' => 1,
                'internal_reference_id' => $internalReferenceId,
                'internal_reference_type' => 'inventory_internal_consumption',
                'created_by' => auth()->id(),
            ]);

            // Link the stock movement to the restaurant expense transaction
            $movement->reference_type = 'App\Models\DailyTransaction';
            $movement->reference_id = $restaurantTransaction->id;
            $movement->save();

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($validated['movement_date']);

            // Log the internal consumption activity
            \App\Models\ActivityLog::log(
                'internal_consumption',
                "Internal Consumption (Dual-Entry): {$item->name} - {$validated['quantity']} {$item->unit} @ {$costBasisLabel} (Total: ₩" . number_format($totalAmount, 0) . ")",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => $validated['quantity'],
                    'unit' => $item->unit,
                    'cost_basis' => $validated['cost_basis'],
                    'price_per_unit' => $pricePerUnit,
                    'total_amount' => $totalAmount,
                    'movement_date' => $validated['movement_date'],
                    'inventory_transaction_id' => $inventoryTransaction->id,
                    'restaurant_transaction_id' => $restaurantTransaction->id,
                    'internal_reference_id' => $internalReferenceId,
                ]
            );

            DB::commit();

            return redirect()->route('inventory.items.index')
                ->with('success', "Internal consumption completed successfully. Dual-entry accounting applied: ₩" . number_format($totalAmount, 0) . " ({$costBasisLabel}).");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process internal consumption: ' . $e->getMessage());
        }
    }

    /**
     * Update balances for transactions after a specific date.
     */
    private function updateSubsequentBalances($fromDate)
    {
        $transactions = DailyTransaction::where('date', '>=', $fromDate)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $previousTransaction = DailyTransaction::where('date', '<', $fromDate)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $runningBalance = $previousTransaction ? $previousTransaction->balance : 0;

        foreach ($transactions as $transaction) {
            $runningBalance = $runningBalance + $transaction->income - $transaction->expense;
            $transaction->balance = $runningBalance;
            $transaction->save();
        }
    }

    /**
     * Show the form for multi-item internal purchase/consumption.
     */
    public function createInternalPurchaseMulti()
    {
        $items = InventoryItem::active()->where('current_stock', '>', 0)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::active()->orderBy('name')->get();
        return view('inventory.movements.internal-purchase-multi', compact('items', 'paymentMethods'));
    }

    /**
     * Store multi-item internal purchase (consume multiple items from own inventory).
     * 
     * DUAL-ENTRY ACCOUNTING (PER ITEM):
     * Creates TWO linked transactions PER ITEM:
     * 1. INVENTORY INCOME - Reduces inventory value (source='inventory')
     * 2. RESTAURANT EXPENSE - Records operational cost (source='restaurant')
     * 
     * All items share the same batch_id, date, and payment method.
     */
    public function storeInternalPurchaseMulti(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_basis' => 'required|in:unit_cost,selling_price',
            'movement_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique batch ID for all items in this multi-item transaction
            $batchId = 'BATCH-' . $validated['movement_date'] . '-' . uniqid();
            $grandTotal = 0;
            $processedItems = [];

            foreach ($validated['items'] as $itemData) {
                $item = InventoryItem::findOrFail($itemData['inventory_item_id']);

                // Check if sufficient stock
                if ($item->current_stock < $itemData['quantity']) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Insufficient stock for {$item->name}. Available: {$item->current_stock} {$item->unit}");
                }

                // Determine pricing based on selected cost basis
                $pricePerUnit = $itemData['cost_basis'] === 'unit_cost' 
                    ? $item->unit_cost 
                    : $item->selling_price_per_unit;
                
                $totalAmount = $itemData['quantity'] * $pricePerUnit;
                $costBasisLabel = $itemData['cost_basis'] === 'unit_cost' 
                    ? 'Unit Cost' 
                    : 'Selling Price';
                
                // Generate unique reference ID for linking both transactions for this item
                $internalReferenceId = 'IIC-' . $validated['movement_date'] . '-' . uniqid();

                // Create stock movement for internal consumption
                $movement = StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'internal_purchase',
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->unit_cost * $itemData['quantity'],
                    'balance_after' => $item->current_stock - $itemData['quantity'],
                    'movement_date' => $validated['movement_date'],
                    'notes' => ($validated['notes'] ?? 'Multi-item internal consumption') . " (Basis: {$costBasisLabel})",
                    'created_by' => auth()->id(),
                ]);

                // Update item stock
                $item->updateStock($itemData['quantity'], 'out');

                // Find or create income category for inventory
                $inventoryIncomeCategory = Category::where('type', 'income')
                    ->where(function($query) {
                        $query->where('name', 'LIKE', '%inventory%')
                              ->orWhere('name', 'LIKE', '%internal%');
                    })
                    ->first();

                if (!$inventoryIncomeCategory) {
                    $inventoryIncomeCategory = Category::create([
                        'name' => 'Inventory Internal Usage',
                        'type' => 'income',
                    ]);
                }

                // Get previous balance for calculation
                $previousTransaction = DailyTransaction::orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $previousBalance = $previousTransaction ? $previousTransaction->balance : 0;

                // Create INVENTORY INCOME transaction
                $inventoryTransaction = DailyTransaction::create([
                    'date' => $validated['movement_date'],
                    'description' => "Inventory internal usage – {$item->name} ({$itemData['quantity']} {$item->unit})",
                    'source' => 'inventory',
                    'income' => $totalAmount,
                    'expense' => 0,
                    'balance' => $previousBalance + $totalAmount,
                    'category_id' => $inventoryIncomeCategory->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'currency_id' => 1,
                    'amount_original' => $totalAmount,
                    'amount_base' => $totalAmount,
                    'exchange_rate_snapshot' => 1,
                    'internal_reference_id' => $internalReferenceId,
                    'internal_reference_type' => 'inventory_internal_consumption',
                    'batch_id' => $batchId,
                    'created_by' => auth()->id(),
                ]);

                // Find or create expense category for restaurant consumption
                $restaurantExpenseCategory = Category::where('type', 'expense')
                    ->where(function($query) {
                        $query->where('name', 'LIKE', '%ingredient%')
                              ->orWhere('name', 'LIKE', '%consumption%')
                              ->orWhere('name', 'LIKE', '%inventory%');
                    })
                    ->first();

                if (!$restaurantExpenseCategory) {
                    $restaurantExpenseCategory = Category::create([
                        'name' => 'Ingredients / Inventory Consumption',
                        'type' => 'expense',
                    ]);
                }

                // Get updated balance after inventory transaction
                $currentBalance = $inventoryTransaction->balance;

                // Create RESTAURANT EXPENSE transaction
                $restaurantTransaction = DailyTransaction::create([
                    'date' => $validated['movement_date'],
                    'description' => "Inventory consumed by restaurant – {$item->name}" . 
                                    ($validated['notes'] ? " – {$validated['notes']}" : "") . 
                                    " (@ {$costBasisLabel})",
                    'source' => 'restaurant',
                    'income' => 0,
                    'expense' => $totalAmount,
                    'balance' => $currentBalance - $totalAmount,
                    'category_id' => $restaurantExpenseCategory->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'currency_id' => 1,
                    'amount_original' => $totalAmount,
                    'amount_base' => $totalAmount,
                    'exchange_rate_snapshot' => 1,
                    'internal_reference_id' => $internalReferenceId,
                    'internal_reference_type' => 'inventory_internal_consumption',
                    'batch_id' => $batchId,
                    'created_by' => auth()->id(),
                ]);

                // Link the stock movement to the restaurant expense transaction
                $movement->reference_type = 'App\Models\DailyTransaction';
                $movement->reference_id = $restaurantTransaction->id;
                $movement->save();

                $grandTotal += $totalAmount;
                $processedItems[] = "{$item->name} ({$itemData['quantity']} {$item->unit})";
            }

            // Update balances for subsequent transactions
            $this->updateSubsequentBalances($validated['movement_date']);

            // Log the multi-item internal consumption activity
            \App\Models\ActivityLog::log(
                'internal_consumption_multi',
                "Multi-Item Internal Consumption: " . count($validated['items']) . " items - Total: ₩" . number_format($grandTotal, 0),
                'inventory',
                [
                    'batch_id' => $batchId,
                    'item_count' => count($validated['items']),
                    'items' => $processedItems,
                    'grand_total' => $grandTotal,
                    'movement_date' => $validated['movement_date'],
                ]
            );

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', "Multi-item internal consumption completed successfully. " . count($validated['items']) . " items processed. Total: ₩" . number_format($grandTotal, 0));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process multi-item internal consumption: ' . $e->getMessage());
        }
    }
}
