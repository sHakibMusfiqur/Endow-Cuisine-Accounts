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
}
