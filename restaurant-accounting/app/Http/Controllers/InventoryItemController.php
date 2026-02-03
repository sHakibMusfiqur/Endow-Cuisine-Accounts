<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Services\PurchaseCorrectionService;
use App\Services\InventoryItemDeletionService;
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
            'selling_price_per_unit' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'adjusted_stock' => 'nullable|numeric|min:0',
            'corrected_unit_cost' => 'nullable|numeric|min:0',
            'adjustment_notes' => 'nullable|string|max:1000',
            'correction_type' => 'nullable|string|in:purchase_correction,inventory_adjustment,damage_spoilage',
        ]);

        // Validate: If stock or unit cost adjustment is provided, notes and correction type are required
        if ($request->filled('adjusted_stock') || $request->filled('corrected_unit_cost')) {
            if (!$request->filled('adjustment_notes')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['adjustment_notes' => 'Adjustment reason/notes are required when correcting stock or unit cost.']);
            }
            
            if (!$request->filled('correction_type')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['correction_type' => 'Correction type is required when adjusting stock or unit cost.']);
            }
            
            // Unit cost correction is only allowed with purchase_correction type
            if ($request->filled('corrected_unit_cost') && $request->correction_type !== 'purchase_correction') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['corrected_unit_cost' => 'Unit cost correction is only allowed with Purchase Entry Correction type.']);
            }
            
            // Damage/Spoilage specific validation
            // For damage, adjusted_stock represents the DAMAGE QUANTITY (amount lost), not the corrected total
            if ($request->correction_type === 'damage_spoilage') {
                $damageQuantity = (float) $request->adjusted_stock;
                $currentStock = (float) $item->current_stock;
                
                // Damage quantity must be positive
                if ($damageQuantity <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['adjusted_stock' => 'Damage quantity must be greater than 0.']);
                }
                
                // Damage cannot exceed current stock
                if ($damageQuantity > $currentStock) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['adjusted_stock' => 'Damage quantity (' . number_format($damageQuantity, 2) . ' ' . $item->unit . ') cannot exceed current stock (' . number_format($currentStock, 2) . ' ' . $item->unit . ').']);
                }
            }
            
            // Purchase Correction specific validation
            if ($request->correction_type === 'purchase_correction') {
                $correctedStock = (float) $request->adjusted_stock;
                
                // Corrected stock cannot be negative
                if ($correctedStock < 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['adjusted_stock' => 'Corrected stock quantity cannot be negative.']);
                }
            }
        }

        DB::beginTransaction();
        
        try {
            // is_active is already validated as boolean (0 or 1), cast to ensure type safety
            $validated['is_active'] = (bool) $validated['is_active'];

            // Track changes for activity log (excluding adjustment fields)
            $itemData = collect($validated)->except(['adjusted_stock', 'corrected_unit_cost', 'adjustment_notes', 'correction_type'])->toArray();
            $changes = array_diff_assoc($itemData, $item->only(array_keys($itemData)));

            // Update basic item information (NOT stock quantity or unit cost yet)
            $item->update($itemData);

            $correctionMessage = '';
            $correctionMade = false;

            // Handle stock corrections based on correction type
            if ($request->filled('correction_type') && ($request->filled('adjusted_stock') || $request->filled('corrected_unit_cost'))) {
                $oldStock = (float) $item->current_stock;
                
                // IMPORTANT: For damage_spoilage, adjusted_stock is the DAMAGE QUANTITY (amount lost)
                // For other types, adjusted_stock is the CORRECTED TOTAL QUANTITY
                if ($validated['correction_type'] === 'damage_spoilage') {
                    $damageQuantity = (float) $validated['adjusted_stock'];
                    $newStock = $oldStock - $damageQuantity; // Calculate new stock after damage
                } else {
                    $newStock = $request->filled('adjusted_stock') ? (float) $validated['adjusted_stock'] : $oldStock;
                }
                
                // Only proceed if there's an actual change in stock or unit cost
                if ($oldStock != $newStock || $request->filled('corrected_unit_cost')) {
                    $correctionService = new PurchaseCorrectionService();
                    
                    switch ($validated['correction_type']) {
                        case 'purchase_correction':
                            // Purchase Entry Correction: Update original expense
                            $correctedUnitCost = $request->filled('corrected_unit_cost') ? (float) $validated['corrected_unit_cost'] : null;
                            
                            $result = $correctionService->correctPurchaseEntry(
                                $item,
                                $oldStock,
                                $newStock,
                                $correctedUnitCost,
                                $validated['adjustment_notes']
                            );
                            
                            // Build correction message
                            $correctionParts = [];
                            if ($oldStock != $newStock) {
                                $correctionParts[] = sprintf(
                                    'quantity corrected from %s to %s %s',
                                    number_format($oldStock, 2),
                                    number_format($newStock, 2),
                                    $item->unit
                                );
                            }
                            if ($correctedUnitCost !== null && $correctedUnitCost != $result['old_unit_cost']) {
                                $correctionParts[] = sprintf(
                                    'unit cost corrected from ₩%s to ₩%s',
                                    number_format($result['old_unit_cost'], 2),
                                    number_format($correctedUnitCost, 2)
                                );
                            }
                            
                            $correctionMessage = sprintf(
                                ' Purchase Entry Correction: %s. Original expense updated from ₩%s to ₩%s (Transaction #%d).',
                                implode(', ', $correctionParts),
                                number_format($result['old_expense'], 0),
                                number_format($result['corrected_expense'], 0),
                                $result['transaction_id']
                            );
                            $correctionMade = true;
                            break;
                            
                        case 'inventory_adjustment':
                            // Non-financial adjustment (physical count correction)
                            $result = $correctionService->adjustInventory(
                                $item,
                                $oldStock,
                                $newStock,
                                $validated['adjustment_notes']
                            );
                            
                            $correctionMessage = sprintf(
                                ' Stock adjusted from %s to %s %s (no financial impact).',
                                number_format($oldStock, 2),
                                number_format($newStock, 2),
                                $item->unit
                            );
                            $correctionMade = true;
                            break;
                            
                        case 'damage_spoilage':
                            // Record inventory loss (NO FINANCIAL IMPACT)
                            // This reduces inventory asset only, does not affect expenses or cash
                            $damageQuantity = (float) $validated['adjusted_stock'];
                            
                            $result = $correctionService->recordDamageSpoilage(
                                $item,
                                $oldStock,
                                $newStock,
                                $validated['adjustment_notes']
                            );
                            
                            $correctionMessage = sprintf(
                                ' Damage/Spoilage recorded. %s %s lost. Stock reduced from %s to %s %s. Inventory write-down value: ₩%s (NO expense transaction created - inventory-only adjustment).',
                                number_format($damageQuantity, 2),
                                $item->unit,
                                number_format($oldStock, 2),
                                number_format($newStock, 2),
                                $item->unit,
                                number_format($result['loss_value'], 0)
                            );
                            $correctionMade = true;
                            break;
                    }
                }
            }

            DB::commit();

            $message = 'Inventory item updated successfully.' . $correctionMessage;

            return redirect()->route('inventory.items.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified inventory item and ALL related data.
     * 
     * CRITICAL BUSINESS RULE:
     * When an inventory item is deleted, ALL data related to that item
     * must be completely removed and all financial summaries must update automatically.
     * 
     * This includes:
     * - The inventory item itself
     * - All stock movements (purchases, sales, usage, adjustments)
     * - All inventory adjustments (corrections, damage/spoilage)
     * - All usage recipes
     * - All financial transactions generated from this item
     * 
     * After deletion:
     * - Dashboard totals recalculate automatically
     * - Reports & Analytics reflect updated values
     * - No stale balances or cached values
     * - No orphan transactions
     * 
     * Authorization: Admin and Accountant users only.
     */
    public function destroy(InventoryItem $item)
    {
        // Check if user has delete inventory permission (Admin only)
        if (!Auth::user()->can('delete inventory')) {
            return redirect()->back()
                ->with('error', 'You do not have permission to delete inventory items.');
        }

        try {
            // Get deletion impact for detailed logging
            $deletionService = new InventoryItemDeletionService();
            $impact = $deletionService->getDeletionImpact($item);

            // Log the deletion attempt before proceeding
            \App\Models\ActivityLog::log(
                'delete',
                "Attempting to delete inventory item: {$item->name} (SKU: {$item->sku}, Stock: {$item->current_stock} {$item->unit})",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'current_stock' => $item->current_stock,
                    'unit' => $item->unit,
                    'impact' => $impact,
                ]
            );

            // Perform comprehensive deletion using the service
            $summary = $deletionService->deleteInventoryItem($item);

            // Log successful deletion with complete summary
            \App\Models\ActivityLog::log(
                'delete',
                "Successfully deleted inventory item: {$summary['item_name']} and all related data",
                'inventory',
                [
                    'summary' => $summary,
                ]
            );

            // Build success message with deletion details
            $message = sprintf(
                'Inventory item "%s" deleted successfully. Removed: %d transactions, %d stock movements, %d adjustments, %d usage recipes.',
                $summary['item_name'],
                $summary['deleted_records']['transactions'] ?? 0,
                $summary['deleted_records']['stock_movements'] ?? 0,
                $summary['deleted_records']['adjustments'] ?? 0,
                $summary['deleted_records']['usage_recipes'] ?? 0
            );

            return redirect()->route('inventory.items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            // Log the error
            \App\Models\ActivityLog::log(
                'error',
                "Failed to delete inventory item: {$item->name}",
                'inventory',
                [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'error' => $e->getMessage(),
                ]
            );

            return redirect()->back()
                ->with('error', 'Failed to delete inventory item: ' . $e->getMessage());
        }
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
