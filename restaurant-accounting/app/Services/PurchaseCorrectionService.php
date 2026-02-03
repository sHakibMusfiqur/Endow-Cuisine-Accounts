<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\DailyTransaction;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Service to handle purchase entry corrections in a financially-sound manner.
 * 
 * This service ensures that when a purchase quantity was entered incorrectly,
 * the correction updates the original expense transaction rather than creating
 * new accounting entries.
 */
class PurchaseCorrectionService
{
    /**
     * Correct a purchase entry error by updating the original transaction.
     * 
     * @param InventoryItem $item The inventory item to correct
     * @param float $oldQuantity The incorrect quantity that was entered
     * @param float $correctedQuantity The correct purchase quantity
     * @param float|null $correctedUnitCost The correct unit cost (optional, null means no change)
     * @param string $notes Explanation of the correction
     * @return array Status information about the correction
     * @throws \Exception If the correction cannot be completed
     */
    public function correctPurchaseEntry(
        InventoryItem $item,
        float $oldQuantity,
        float $correctedQuantity,
        ?float $correctedUnitCost,
        string $notes
    ): array {
        // Find the most recent purchase (stock 'in') for this item
        $lastPurchase = StockMovement::where('inventory_item_id', $item->id)
            ->whereIn('type', ['in', 'opening'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastPurchase) {
            throw new \Exception('No purchase record found for this item. Cannot correct a non-existent purchase.');
        }

        // Store old unit cost for tracking
        $oldUnitCost = $item->unit_cost;
        
        // Use corrected unit cost if provided, otherwise use current unit cost
        $effectiveUnitCost = $correctedUnitCost ?? $item->unit_cost;
        
        // Calculate the difference
        $quantityDifference = $correctedQuantity - $oldQuantity;
        
        // Calculate old and new expense amounts
        $oldExpenseAmount = $oldQuantity * $oldUnitCost;
        $correctedExpenseAmount = $correctedQuantity * $effectiveUnitCost;
        $expenseDifference = $correctedExpenseAmount - $oldExpenseAmount;

        // Find the related expense transaction
        // Match by date and approximate amount (within 1% tolerance for rounding)
        $purchaseDate = $lastPurchase->movement_date;
        $expenseTransaction = DailyTransaction::where('date', $purchaseDate)
            ->where('expense', '>', 0)
            ->where('description', 'LIKE', '%' . $item->name . '%')
            ->whereBetween('expense', [$oldExpenseAmount * 0.99, $oldExpenseAmount * 1.01])
            ->orderBy('id', 'desc')
            ->first();

        // If we can't find an exact match, find the closest expense transaction on that date
        if (!$expenseTransaction) {
            $expenseTransaction = DailyTransaction::where('date', $purchaseDate)
                ->where('expense', '>', 0)
                ->where('description', 'LIKE', '%' . $item->name . '%')
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$expenseTransaction) {
            throw new \Exception('Cannot find the original purchase expense transaction to correct.');
        }

        // Start correction process
        $result = [
            'stock_updated' => false,
            'unit_cost_updated' => false,
            'expense_updated' => false,
            'old_unit_cost' => $oldUnitCost,
            'corrected_unit_cost' => $effectiveUnitCost,
            'old_expense' => $expenseTransaction->expense,
            'corrected_expense' => $correctedExpenseAmount,
            'expense_difference' => $expenseDifference,
            'transaction_id' => $expenseTransaction->id,
        ];

        // Update the item's current stock
        $item->current_stock = $correctedQuantity;
        
        // Update unit cost if a corrected value was provided
        if ($correctedUnitCost !== null) {
            $item->unit_cost = $correctedUnitCost;
            $result['unit_cost_updated'] = true;
        }
        
        // Note: stock_value is a computed attribute (current_stock * unit_cost)
        // It's calculated via the model's getStockValueAttribute() accessor
        
        $item->save();
        $result['stock_updated'] = true;

        // Update the expense transaction to reflect the correct purchase amount
        $oldBalance = $expenseTransaction->balance;
        $expenseTransaction->expense = $correctedExpenseAmount;
        
        // CRITICAL: Also update amount_base and amount_original to match
        // This ensures dashboard summaries reflect the corrected values
        $expenseTransaction->amount_original = $correctedExpenseAmount;
        $expenseTransaction->amount_base = $correctedExpenseAmount;
        
        // Update the description to note it was corrected
        if (!str_contains($expenseTransaction->description, '[CORRECTED]')) {
            $expenseTransaction->description = $expenseTransaction->description . ' [CORRECTED]';
        }
        
        $expenseTransaction->save();
        $result['expense_updated'] = true;

        // Recalculate balances for all subsequent transactions
        $this->recalculateBalancesAfter($expenseTransaction);

        // Update the stock movement record
        $lastPurchase->quantity = $correctedQuantity;
        $lastPurchase->unit_cost = $effectiveUnitCost;
        $lastPurchase->total_cost = $correctedExpenseAmount;
        $lastPurchase->balance_after = $correctedQuantity;
        $lastPurchase->notes = ($lastPurchase->notes ?? '') . ' [CORRECTED: ' . $notes . ']';
        $lastPurchase->save();

        // Log the purchase correction in inventory_adjustments
        $adjustmentNotes = sprintf(
            'Purchase corrected from %s to %s %s',
            number_format($oldQuantity, 2),
            number_format($correctedQuantity, 2),
            $item->unit
        );
        
        if ($correctedUnitCost !== null && $oldUnitCost != $correctedUnitCost) {
            $adjustmentNotes .= sprintf(
                ', unit cost corrected from ₩%s to ₩%s',
                number_format($oldUnitCost, 2),
                number_format($correctedUnitCost, 2)
            );
        }
        
        $adjustmentNotes .= sprintf(
            '. Expense updated from ₩%s to ₩%s. Reason: %s',
            number_format($oldExpenseAmount, 0),
            number_format($correctedExpenseAmount, 0),
            $notes
        );
        
        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $correctedQuantity,
            'difference' => $quantityDifference,
            'old_expense_amount' => $oldExpenseAmount,
            'corrected_expense_amount' => $correctedExpenseAmount,
            'expense_transaction_id' => $expenseTransaction->id,
            'reason' => 'Purchase Entry Correction',
            'correction_type' => 'purchase_correction',
            'notes' => $adjustmentNotes,
            'adjusted_by' => Auth::id(),
        ]);

        // Log in activity log
        $activityData = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'old_quantity' => $oldQuantity,
            'corrected_quantity' => $correctedQuantity,
            'quantity_difference' => $quantityDifference,
            'old_expense' => $oldExpenseAmount,
            'corrected_expense' => $correctedExpenseAmount,
            'expense_difference' => $expenseDifference,
            'transaction_id' => $expenseTransaction->id,
            'notes' => $notes,
        ];
        
        if ($correctedUnitCost !== null) {
            $activityData['old_unit_cost'] = $oldUnitCost;
            $activityData['corrected_unit_cost'] = $correctedUnitCost;
        }
        
        \App\Models\ActivityLog::log(
            'purchase_correction',
            "Purchase Entry Correction: {$item->name}",
            'inventory',
            $activityData
        );

        // ==========================================
        // SPATIE ACTIVITY LOG FOR AUDIT COMPLIANCE
        // ==========================================
        $spatieProperties = [
            'item' => $item->name,
            'old_qty' => $oldQuantity,
            'new_qty' => $correctedQuantity,
            'old_amount' => $oldExpenseAmount,
            'new_amount' => $correctedExpenseAmount,
            'correction_type' => 'purchase_correction',
        ];
        
        if ($correctedUnitCost !== null) {
            $spatieProperties['old_unit_cost'] = $oldUnitCost;
            $spatieProperties['new_unit_cost'] = $correctedUnitCost;
        }
        
        activity()
            ->useLog('inventory')
            ->causedBy(auth()->user())
            ->performedOn($expenseTransaction)
            ->withProperties($spatieProperties)
            ->log("Purchase Entry Corrected – {$item->name} ({$oldQuantity} → {$correctedQuantity})");

        return $result;
    }

    /**
     * Handle damage or spoilage - INVENTORY-ONLY, NO FINANCIAL IMPACT.
     * 
     * CRITICAL ACCOUNTING RULE:
     * Damage/Spoilage reduces inventory asset ONLY. It does NOT create expenses,
     * affect cash balance, or modify daily transactions because the item was already
     * purchased (expense already recorded). This is purely an inventory write-down.
     * 
     * @param InventoryItem $item The inventory item
     * @param float $oldQuantity Current stock before loss
     * @param float $correctedQuantity Stock after loss
     * @param string $notes Explanation of the loss (REQUIRED)
     * @return array Status information
     * @throws \Exception If validation fails
     */
    public function recordDamageSpoilage(
        InventoryItem $item,
        float $oldQuantity,
        float $correctedQuantity,
        string $notes
    ): array {
        // ==========================================
        // VALIDATION: Ensure damage quantity is valid
        // ==========================================
        if ($correctedQuantity >= $oldQuantity) {
            throw new \Exception('For damage/spoilage, corrected quantity must be less than current stock.');
        }

        if ($correctedQuantity < 0) {
            throw new \Exception('Corrected quantity cannot be negative.');
        }

        if (empty(trim($notes))) {
            throw new \Exception('Damage/spoilage reason is required for audit trail.');
        }

        // ==========================================
        // CALCULATE LOSS METRICS
        // ==========================================
        $quantityLost = $oldQuantity - $correctedQuantity;
        $lossValue = $quantityLost * $item->unit_cost; // For reporting only, NOT for accounting

        // ==========================================
        // UPDATE INVENTORY (NO FINANCIAL IMPACT)
        // ==========================================
        $item->current_stock = $correctedQuantity;
        // Note: stock_value is auto-calculated by the model as (current_stock * unit_cost)
        $item->save();

        // ==========================================
        // CREATE STOCK MOVEMENT LOG (TRACEABILITY)
        // Type: 'adjustment' - uses schema-compatible ENUM value
        // Reference Type: 'damage_spoilage' - identifies this as damage (not normal adjustment)
        // Quantity: NEGATIVE to show reduction
        // ==========================================
        StockMovement::create([
            'inventory_item_id' => $item->id,
            'type' => 'adjustment', // ENUM compatible: stock_in, stock_out, adjustment
            'quantity' => -$quantityLost, // Negative value to indicate loss
            'unit_cost' => $item->unit_cost,
            'total_cost' => $lossValue, // Informational only, not used in accounting
            'balance_after' => $correctedQuantity,
            'reference_type' => 'damage_spoilage', // CRITICAL: Distinguishes damage from regular adjustments
            'reference_id' => null, // No transaction reference - this is inventory-only
            'notes' => 'Damage/Spoilage: ' . $notes,
            'movement_date' => now()->toDateString(),
            'created_by' => Auth::id(),
        ]);

        // ==========================================
        // LOG ADJUSTMENT FOR AUDIT TRAIL
        // NO expense_transaction_id because NO transaction is created
        // ==========================================
        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $correctedQuantity,
            'difference' => -$quantityLost, // Negative to show reduction
            'old_expense_amount' => null, // No financial change
            'corrected_expense_amount' => null, // No financial change
            'expense_transaction_id' => null, // IMPORTANT: No transaction created
            'reason' => 'Damage / Spoilage (Inventory Write-Down)',
            'correction_type' => 'damage_spoilage',
            'notes' => sprintf(
                'Inventory Loss: %s %s damaged/spoiled. Estimated value: ₩%s (inventory write-down, no expense transaction created). Reason: %s',
                number_format($quantityLost, 2),
                $item->unit,
                number_format($lossValue, 0),
                $notes
            ),
            'adjusted_by' => Auth::id(),
        ]);

        // ==========================================
        // ACTIVITY LOG
        // ==========================================
        \App\Models\ActivityLog::log(
            'damage_spoilage',
            "Damage/Spoilage recorded: {$item->name}",
            'inventory',
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'old_stock' => $oldQuantity,
                'new_stock' => $correctedQuantity,
                'quantity_lost' => $quantityLost,
                'estimated_loss_value' => $lossValue, // For reporting only
                'unit_cost' => $item->unit_cost,
                'notes' => $notes,
                'accounting_impact' => 'NONE - Inventory write-down only, no expense created',
            ]
        );

        // ==========================================
        // SPATIE ACTIVITY LOG FOR AUDIT COMPLIANCE
        // ==========================================
        activity()
            ->useLog('inventory')
            ->causedBy(auth()->user())
            ->performedOn($item)
            ->withProperties([
                'item' => $item->name,
                'damage_qty' => $quantityLost,
                'unit_cost' => $item->unit_cost,
                'loss_amount' => $lossValue,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $correctedQuantity,
                'correction_type' => 'damage_spoilage',
            ])
            ->log("Inventory Damage – {$item->name} ({$quantityLost} {$item->unit})");

        // ==========================================
        // RETURN RESULT
        // ==========================================
        return [
            'stock_updated' => true,
            'quantity_lost' => $quantityLost,
            'loss_value' => $lossValue, // For UI display only
            'no_financial_impact' => true, // EXPLICIT flag
            'expense_created' => false, // EXPLICIT: No expense
            'transaction_id' => null, // EXPLICIT: No transaction
        ];
    }

    /**
     * Handle non-financial inventory adjustment (physical count corrections).
     * 
     * @param InventoryItem $item The inventory item
     * @param float $oldQuantity Current recorded stock
     * @param float $correctedQuantity Actual physical count
     * @param string $notes Explanation of the adjustment
     * @return array Status information
     */
    public function adjustInventory(
        InventoryItem $item,
        float $oldQuantity,
        float $correctedQuantity,
        string $notes
    ): array {
        $difference = $correctedQuantity - $oldQuantity;

        // Update stock
        $item->current_stock = $correctedQuantity;
        $item->save();

        // Create stock movement (no financial impact)
        StockMovement::create([
            'inventory_item_id' => $item->id,
            'type' => 'adjustment',
            'quantity' => abs($difference),
            'unit_cost' => $item->unit_cost,
            'total_cost' => 0, // No financial impact
            'balance_after' => $correctedQuantity,
            'reference_type' => 'manual_adjustment',
            'reference_id' => null,
            'notes' => 'Inventory Adjustment: ' . $notes,
            'movement_date' => now()->toDateString(),
            'created_by' => Auth::id(),
        ]);

        // Log the adjustment
        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'correction_type' => 'inventory_adjustment',
            'old_quantity' => $oldQuantity,
            'new_quantity' => $correctedQuantity,
            'difference' => $difference,
            'reason' => 'Inventory Adjustment (Non-financial)',
            'notes' => $notes,
            'adjusted_by' => Auth::id(),
        ]);

        \App\Models\ActivityLog::log(
            'inventory_adjustment',
            "Inventory Adjustment: {$item->name}",
            'inventory',
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'old_quantity' => $oldQuantity,
                'corrected_quantity' => $correctedQuantity,
                'difference' => $difference,
                'notes' => $notes,
            ]
        );

        return [
            'stock_updated' => true,
            'no_financial_impact' => true,
            'difference' => $difference,
        ];
    }

    /**
     * Recalculate running balances for all transactions after the given transaction.
     * 
     * @param DailyTransaction $fromTransaction The transaction to start recalculating from
     * @return void
     */
    private function recalculateBalancesAfter(DailyTransaction $fromTransaction): void
    {
        $transactions = DailyTransaction::where('date', '>=', $fromTransaction->date)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $previousTransaction = DailyTransaction::where('date', '<', $fromTransaction->date)
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
