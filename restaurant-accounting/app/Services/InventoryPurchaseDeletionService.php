<?php

namespace App\Services;

use App\Models\DailyTransaction;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service for handling inventory purchase transaction deletions.
 * 
 * This service ensures proper handling when purchase entries are deleted:
 * - Reduces item stock levels by the purchase quantity
 * - Removes stock movement records
 * - Deletes transaction records
 * - Recalculates running balances
 * - Maintains activity logs
 * - Does NOT create reverse transactions
 * 
 * IMPORTANT: This is designed for purchase entry corrections, not regular operations.
 */
class InventoryPurchaseDeletionService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Check if a transaction is an inventory purchase.
     * 
     * @param DailyTransaction $transaction
     * @return bool
     */
    public function isPurchase(DailyTransaction $transaction): bool
    {
        // Check if description matches purchase pattern
        $isPurchaseDescription = (
            str_contains($transaction->description, 'Stock Purchase:') ||
            str_contains($transaction->description, 'Inventory Purchase') ||
            str_contains($transaction->description, 'Opening Stock:')
        );

        // Must be an expense transaction
        $isExpense = $transaction->expense > 0 && $transaction->income == 0;

        // Should have inventory module category
        $isInventoryCategory = $transaction->category && 
                              $transaction->category->module === 'inventory' &&
                              $transaction->category->type === 'expense';

        return $isPurchaseDescription && $isExpense && $isInventoryCategory;
    }

    /**
     * Delete an inventory purchase transaction.
     * 
     * This removes the purchase from the system by:
     * 1. Finding the related stock movement
     * 2. Subtracting the purchase quantity from inventory
     * 3. Deleting the stock movement
     * 4. Deleting the transaction
     * 5. Recalculating balances
     * 
     * NO reverse transaction is created - the purchase is simply removed.
     * 
     * @param DailyTransaction $transaction The purchase transaction to delete
     * @return array ['success' => true, 'message' => '...', 'affected_item' => [...]]
     * @throws Exception
     */
    public function deletePurchase(DailyTransaction $transaction): array
    {
        DB::beginTransaction();

        try {
            // STEP 1: Validate this is a purchase transaction
            if (!$this->isPurchase($transaction)) {
                throw new Exception('This transaction is not an inventory purchase and cannot be deleted using this method.');
            }

            // STEP 2: Find related stock movement
            $stockMovement = $this->findStockMovement($transaction);

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found for this purchase transaction. Cannot safely delete.');
            }

            // STEP 3: Get the inventory item
            $inventoryItem = $stockMovement->inventoryItem;

            if (!$inventoryItem) {
                throw new Exception('Inventory item not found for this stock movement. Data integrity issue.');
            }

            $oldStock = (float) $inventoryItem->current_stock;
            $purchaseQuantity = (float) $stockMovement->quantity;

            // STEP 4: Validate stock won't go negative
            $newStock = $oldStock - $purchaseQuantity;
            
            if ($newStock < 0) {
                throw new Exception(sprintf(
                    'Cannot delete purchase: Stock would become negative (Current: %s, Purchase: %s, Result: %s %s). ' .
                    'Some of this stock may have already been used or sold.',
                    number_format($oldStock, 2),
                    number_format($purchaseQuantity, 2),
                    number_format($newStock, 2),
                    $inventoryItem->unit
                ));
            }

            // STEP 5: Subtract the purchase quantity from inventory
            $inventoryItem->current_stock = $newStock;
            $inventoryItem->save();

            // STEP 6: Update stock value (automatically calculated by model accessor)
            // The stock_value is computed as current_stock * unit_cost via getStockValueAttribute()

            // STEP 7: Delete the stock movement record
            $movementId = $stockMovement->id;
            $movementType = $stockMovement->type;
            $stockMovement->delete();

            // STEP 8: Store transaction info for logging
            $transactionDate = $transaction->date;
            $transactionDescription = $transaction->description;
            $transactionAmount = (float) $transaction->expense;
            $transactionId = $transaction->id;

            // STEP 9: Delete the purchase transaction
            $transaction->delete();

            // STEP 10: Recalculate balances for all subsequent transactions
            $this->transactionService->updateSubsequentBalances($transactionDate);

            // STEP 11: Log the deletion
            $this->logPurchaseDeletion(
                $transactionId,
                $transactionDescription,
                $transactionAmount,
                $transactionDate,
                $inventoryItem,
                $oldStock,
                $newStock,
                $purchaseQuantity,
                $movementId,
                $movementType
            );

            DB::commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'Purchase deleted successfully! Stock reduced from %s to %s %s for %s. Transaction amount: ₩%s removed from expenses.',
                    number_format($oldStock, 2),
                    number_format($newStock, 2),
                    $inventoryItem->unit,
                    $inventoryItem->name,
                    number_format($transactionAmount, 0)
                ),
                'affected_item' => [
                    'item_name' => $inventoryItem->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'removed_quantity' => $purchaseQuantity,
                    'unit' => $inventoryItem->unit,
                    'transaction_amount' => $transactionAmount,
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Find the stock movement associated with a purchase transaction.
     * 
     * @param DailyTransaction $transaction
     * @return StockMovement|null
     */
    protected function findStockMovement(DailyTransaction $transaction): ?StockMovement
    {
        // Try to find by reference_type and reference_id
        $movement = StockMovement::where('reference_type', DailyTransaction::class)
            ->where('reference_id', $transaction->id)
            ->whereIn('type', ['in', 'opening'])
            ->first();

        if ($movement) {
            return $movement;
        }

        // Fallback: Try to find by matching date and description
        // Extract item name from description
        if (preg_match('/Stock Purchase: (.+?) \(/', $transaction->description, $matches) ||
            preg_match('/Opening Stock: (.+?) \(/', $transaction->description, $matches)) {
            
            $itemName = $matches[1];
            $item = InventoryItem::where('name', $itemName)->first();

            if ($item) {
                // Find stock movement for this item on the same date
                $movement = StockMovement::where('inventory_item_id', $item->id)
                    ->where('movement_date', $transaction->date)
                    ->whereIn('type', ['in', 'opening'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                return $movement;
            }
        }

        return null;
    }

    /**
     * Log the purchase deletion activity.
     * 
     * @param int $transactionId
     * @param string $transactionDescription
     * @param float $transactionAmount
     * @param string $transactionDate
     * @param InventoryItem $item
     * @param float $oldStock
     * @param float $newStock
     * @param float $quantity
     * @param int $movementId
     * @param string $movementType
     * @return void
     */
    protected function logPurchaseDeletion(
        int $transactionId,
        string $transactionDescription,
        float $transactionAmount,
        string $transactionDate,
        InventoryItem $item,
        float $oldStock,
        float $newStock,
        float $quantity,
        int $movementId,
        string $movementType
    ): void {
        // Log in custom activity log
        ActivityLog::log(
            'delete',
            "Deleted inventory purchase: {$item->name} - Stock reduced from {$oldStock} to {$newStock} {$item->unit}",
            'inventory',
            [
                'transaction_id' => $transactionId,
                'transaction_description' => $transactionDescription,
                'transaction_amount' => $transactionAmount,
                'transaction_date' => $transactionDate,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'removed_quantity' => $quantity,
                'unit' => $item->unit,
                'stock_movement_id' => $movementId,
                'stock_movement_type' => $movementType,
                'deletion_type' => 'purchase_removal',
            ]
        );

        // Log in Spatie activity log for audit compliance
        activity()
            ->useLog('inventory')
            ->causedBy(auth()->user())
            ->performedOn($item)
            ->withProperties([
                'transaction_id' => $transactionId,
                'item' => $item->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'removed_qty' => $quantity,
                'unit' => $item->unit,
                'transaction_amount' => $transactionAmount,
                'action' => 'purchase_deletion',
            ])
            ->log("Purchase Deleted – {$item->name} (Stock: {$oldStock} → {$newStock} {$item->unit})");
    }
}
