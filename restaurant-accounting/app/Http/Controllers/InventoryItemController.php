<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index()
    {
        $items = InventoryItem::with('stockMovements')
            ->orderBy('name')
            ->paginate(20);

        $lowStockCount = InventoryItem::lowStock()->count();
        $totalValue = InventoryItem::active()->get()->sum('stock_value');

        return view('inventory.items.index', compact('items', 'lowStockCount', 'totalValue'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        $paymentMethods = \App\Models\PaymentMethod::active()->orderBy('name')->get();
        return view('inventory.items.create', compact('paymentMethods'));
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items,sku',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'opening_stock' => 'required|numeric|min:0', // Opening stock must be numeric and non-negative
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price_per_unit' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'payment_method_id' => 'required|exists:payment_methods,id', // Payment method required for opening stock accounting
        ]);

        // Use database transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Set current_stock to opening_stock value
            $validated['current_stock'] = $validated['opening_stock'];
            
            // is_active is already validated as boolean (0 or 1), cast to ensure type safety
            $validated['is_active'] = (bool) $validated['is_active'];
            
            // Remove opening_stock and payment_method_id from validated data as they're not columns in inventory_items table
            $openingStock = $validated['opening_stock'];
            $paymentMethodId = $validated['payment_method_id'];
            unset($validated['opening_stock']);
            unset($validated['payment_method_id']);

            // Create the inventory item
            $item = InventoryItem::create($validated);

            // Log the creation activity
            \App\Models\ActivityLog::log(
                'create',
                "Created inventory item: {$item->name} with opening stock of {$openingStock} {$item->unit}",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'opening_stock' => $openingStock,
                    'unit' => $item->unit,
                    'unit_cost' => $item->unit_cost,
                ]
            );

            // Create opening stock movement record for audit trail (only if opening stock > 0)
            if ($openingStock > 0) {
                $totalCost = $openingStock * $item->unit_cost;
                
                StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'opening',
                    'quantity' => $openingStock,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $totalCost,
                    'balance_after' => $openingStock,
                    'reference_type' => 'opening_balance',
                    'reference_id' => null,
                    'notes' => 'Opening stock at item creation',
                    'movement_date' => now()->toDateString(),
                    'created_by' => Auth::id(),
                ]);

                // Create expense transaction for opening stock purchase
                // Opening stock = money spent to acquire initial inventory
                $expenseCategory = \App\Models\Category::where('type', 'expense')
                    ->where(function($query) {
                        $query->where('name', 'LIKE', '%inventory%')
                              ->orWhere('name', 'LIKE', '%purchase%')
                              ->orWhere('name', 'LIKE', '%food%')
                              ->orWhere('name', 'LIKE', '%supplies%');
                    })
                    ->first();

                if (!$expenseCategory) {
                    // Create default inventory expense category
                    $expenseCategory = \App\Models\Category::create([
                        'name' => 'Inventory Purchase',
                        'type' => 'expense',
                    ]);
                }

                // Get previous balance to calculate new balance
                $previousTransaction = \App\Models\DailyTransaction::orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $previousBalance = $previousTransaction ? $previousTransaction->balance : 0;

                \App\Models\DailyTransaction::create([
                    'date' => now()->toDateString(),
                    'description' => "Opening Stock: {$item->name} ({$openingStock} {$item->unit})",
                    'income' => 0,
                    'expense' => $totalCost,
                    'balance' => $previousBalance - $totalCost,
                    'category_id' => $expenseCategory->id,
                    'payment_method_id' => $paymentMethodId,
                    'currency_id' => 1, // Default to KRW
                    'amount_original' => $totalCost,
                    'amount_base' => $totalCost,
                    'exchange_rate_snapshot' => 1,
                    'created_by' => Auth::id(),
                ]);

                // Update balances for subsequent transactions
                $this->updateSubsequentBalances(now()->toDateString());
            }

            DB::commit();

            return redirect()->route('inventory.items.index')
                ->with('success', 'Inventory item created successfully with opening stock of ' . number_format($openingStock, 2) . ' ' . $item->unit . '.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show(InventoryItem $item)
    {
        $item->load(['stockMovements' => function($query) {
            $query->orderBy('movement_date', 'desc')->orderBy('created_at', 'desc');
        }, 'stockMovements.creator', 'usageRecipes.category']);

        return view('inventory.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(InventoryItem $item)
    {
        return view('inventory.items.edit', compact('item'));
    }

    /**
     * Update the specified inventory item.
     */
    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price_per_unit' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        // is_active is already validated as boolean (0 or 1), cast to ensure type safety
        $validated['is_active'] = (bool) $validated['is_active'];

        // Log the update activity
        $changes = array_diff_assoc($validated, $item->only(array_keys($validated)));
        \App\Models\ActivityLog::log(
            'update',
            "Updated inventory item: {$item->name}",
            'inventory',
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'changes' => $changes,
            ]
        );

        $item->update($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified inventory item.
     * Admin and Accountant users can delete items.
     * They can delete any item regardless of stock movements or active status.
     */
    public function destroy(InventoryItem $item)
    {
        // Check if user is Admin or Accountant
        if (!Auth::user()->hasRole(['admin', 'accountant'])) {
            return redirect()->back()
                ->with('error', 'You do not have permission to delete inventory items.');
        }

        // Log the deletion activity
        \App\Models\ActivityLog::log(
            'delete',
            "Deleted inventory item: {$item->name} (SKU: {$item->sku}, Stock: {$item->current_stock} {$item->unit})",
            'inventory',
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'sku' => $item->sku,
                'current_stock' => $item->current_stock,
                'unit' => $item->unit,
            ]
        );

        // Admin and Accountant can delete any item - no restrictions
        // Note: Related stock movements will be handled by database cascade if configured,
        // or remain as orphaned records depending on your database schema
        $item->delete();

        return redirect()->route('inventory.items.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    /**
     * Display low stock items.
     */
    public function lowStock()
    {
        $items = InventoryItem::lowStock()
            ->active()
            ->orderBy('current_stock', 'asc')
            ->paginate(20);

        return view('inventory.items.low-stock', compact('items'));
    }

    /**
     * Update balances for transactions after a specific date.
     * This ensures the running balance is correct after adding new transactions.
     */
    private function updateSubsequentBalances($fromDate)
    {
        $transactions = \App\Models\DailyTransaction::where('date', '>=', $fromDate)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $previousTransaction = \App\Models\DailyTransaction::where('date', '<', $fromDate)
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
}
