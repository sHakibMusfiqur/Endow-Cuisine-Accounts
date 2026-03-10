<?php

namespace App\Services;

use App\Models\DailyTransaction;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for handling inventory sale transaction deletions.
 * 
 * This service ensures proper restoration of inventory when sales are deleted:
 * - Restores item stock levels
 * - Removes/reverses stock movement records
 * - Deletes transaction records
 * - Recalculates running balances
 * - Maintains activity logs
 * - Handles both single and multi-item sales
 */
class InventorySaleDeletionService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Delete an inventory sale transaction with option to restore stock.
     * 
     * @param DailyTransaction $transaction The transaction to delete
     * @param bool $restoreStock If true, restore inventory stock; if false, delete permanently without restoring
     * @param bool $skipBatchCheck If true, skip batch validation (used when deleting entire batch)
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...] or null]
     * @throws Exception
     */
    public function deleteInventorySaleWithOption(DailyTransaction $transaction, bool $restoreStock = true, bool $skipBatchCheck = false): array
    {
        DB::beginTransaction();

        try {
            // STEP 1: Validate this is an inventory sale transaction
            $this->validateInventorySale($transaction);

            // STEP 2: Check if this is part of a batch (multi-item sale)
            if (!$skipBatchCheck && $transaction->batch_id) {
                // Get all transactions in this batch
                $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                    ->orderBy('id')
                    ->get();

                if ($batchTransactions->count() > 1) {
                    // This is a multi-item sale - delete the entire batch
                    return $this->deleteBatchInventorySaleWithOption($transaction->batch_id, $restoreStock);
                }
            }

            // STEP 3: Find related stock movement
            $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                ->where('reference_id', $transaction->id)
                ->where('type', 'sale')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found for this inventory sale transaction.');
            }

            // STEP 4: Validate deletion is safe
            $this->validateSafeDeletion($transaction, $stockMovement, $restoreStock);

            $inventoryItem = $stockMovement->inventoryItem;
            $oldStock = (float) $inventoryItem->current_stock;
            $quantity = (float) $stockMovement->quantity;
            $restoredItems = null;

            // STEP 5: Conditionally restore inventory stock
            if ($restoreStock) {
                $inventoryItem->current_stock += $quantity;
                $inventoryItem->save();
                $newStock = $inventoryItem->current_stock;

                $restoredItems = [
                    [
                        'item_name' => $inventoryItem->name,
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'restored_quantity' => $quantity,
                        'unit' => $inventoryItem->unit,
                    ]
                ];

                // STEP 6: Delete stock movement record
                $stockMovement->delete();

                // STEP 7: Log the deletion with restoration
                $this->logInventorySaleDeletionWithRestore($transaction, $inventoryItem, $oldStock, $newStock, $quantity);
            } else {
                // Permanent deletion without restoration
                // Keep stock movement but mark it as orphaned or just delete it
                $stockMovement->delete();

                // STEP 7: Log the permanent deletion
                $this->logInventorySalePermanentDeletion($transaction, $inventoryItem, $quantity);
            }

            // STEP 8: Delete the transaction
            $date = $transaction->date;
            $transaction->delete();

            // STEP 9: Recalculate balances for all subsequent transactions
            $this->transactionService->updateSubsequentBalances($date);

            DB::commit();

            if ($restoreStock) {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Inventory sale deleted successfully! Stock restored from %s to %s %s for %s',
                        number_format($oldStock, 2),
                        number_format($restoredItems[0]['new_stock'], 2),
                        $inventoryItem->unit,
                        $inventoryItem->name
                    ),
                    'restored_items' => $restoredItems
                ];
            } else {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Inventory sale deleted permanently without restoring stock for %s (Qty: %s %s)',
                        $inventoryItem->name,
                        number_format($quantity, 2),
                        $inventoryItem->unit
                    ),
                    'restored_items' => null
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an inventory sale transaction and restore inventory stock (legacy method).
     * 
     * @param DailyTransaction $transaction The transaction to delete
     * @param bool $skipBatchCheck If true, skip batch validation (used when deleting entire batch)
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...]]
     * @throws Exception
     */
    public function deleteInventorySale(DailyTransaction $transaction, bool $skipBatchCheck = false): array
    {
        DB::beginTransaction();

        try {
            // STEP 1: Validate this is an inventory sale transaction
            $this->validateInventorySale($transaction);

            // STEP 2: Check if this is part of a batch (multi-item sale)
            if (!$skipBatchCheck && $transaction->batch_id) {
                // Get all transactions in this batch
                $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                    ->orderBy('id')
                    ->get();

                if ($batchTransactions->count() > 1) {
                    // This is a multi-item sale - delete the entire batch
                    return $this->deleteBatchInventorySale($transaction->batch_id);
                }
            }

            // STEP 3: Find related stock movement
            $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                ->where('reference_id', $transaction->id)
                ->where('type', 'sale')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found for this inventory sale transaction.');
            }

            // STEP 4: Validate deletion is safe
            $this->validateSafeDeletion($transaction, $stockMovement);

            // STEP 5: Restore inventory stock
            $inventoryItem = $stockMovement->inventoryItem;
            $oldStock = (float) $inventoryItem->current_stock;
            $restoredQuantity = (float) $stockMovement->quantity;
            
            $inventoryItem->current_stock += $restoredQuantity;
            $inventoryItem->save();

            $newStock = $inventoryItem->current_stock;

            // STEP 6: Delete stock movement record
            $stockMovement->delete();

            // STEP 7: Log the deletion before deleting transaction
            $this->logInventorySaleDeletion($transaction, $inventoryItem, $oldStock, $newStock, $restoredQuantity);

            // STEP 8: Delete the transaction (this also recalculates balances)
            $date = $transaction->date;
            $transaction->delete();

            // STEP 9: Recalculate balances for all subsequent transactions
            $this->transactionService->updateSubsequentBalances($date);

            DB::commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'Inventory sale deleted successfully! Stock restored from %s to %s %s for %s',
                    number_format((float)$oldStock, 2),
                    number_format((float)$newStock, 2),
                    $inventoryItem->unit,
                    $inventoryItem->name
                ),
                'restored_items' => [
                    [
                        'item_name' => $inventoryItem->name,
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'restored_quantity' => $restoredQuantity,
                        'unit' => $inventoryItem->unit,
                    ]
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an entire batch of inventory sale transactions with restore option.
     * 
     * @param string $batchId
     * @param bool $restoreStock
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...] or null]
     * @throws Exception
     */
    public function deleteBatchInventorySaleWithOption(string $batchId, bool $restoreStock = true): array
    {
        DB::beginTransaction();

        try {
            // Get all transactions in the batch
            $batchTransactions = DailyTransaction::where('batch_id', $batchId)
                ->orderBy('id')
                ->get();

            if ($batchTransactions->isEmpty()) {
                throw new Exception('No transactions found with batch ID: ' . $batchId);
            }

            $restoredItems = [];
            $itemNames = [];
            $firstTransactionDate = $batchTransactions->first()->date;

            // Process each transaction in the batch
            foreach ($batchTransactions as $transaction) {
                // Validate this is an inventory sale transaction
                $this->validateInventorySale($transaction);

                // Find related stock movement
                $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                    ->where('reference_id', $transaction->id)
                    ->where('type', 'sale')
                    ->first();

                if (!$stockMovement) {
                    throw new Exception("Stock movement record not found for transaction ID: {$transaction->id}");
                }

                // Validate deletion is safe
                $this->validateSafeDeletion($transaction, $stockMovement, $restoreStock);

                $inventoryItem = $stockMovement->inventoryItem;
                $oldStock = (float) $inventoryItem->current_stock;
                $quantity = (float) $stockMovement->quantity;
                $itemNames[] = $inventoryItem->name;

                if ($restoreStock) {
                    // Restore inventory stock
                    $inventoryItem->current_stock += $quantity;
                    $inventoryItem->save();
                    $newStock = $inventoryItem->current_stock;

                    // Track restored item for response
                    $restoredItems[] = [
                        'item_name' => $inventoryItem->name,
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'restored_quantity' => $quantity,
                        'unit' => $inventoryItem->unit,
                    ];

                    // Log the deletion
                    $this->logInventorySaleDeletionWithRestore($transaction, $inventoryItem, $oldStock, $newStock, $quantity);
                } else {
                    // Permanent deletion without restoration
                    $this->logInventorySalePermanentDeletion($transaction, $inventoryItem, $quantity);
                }

                // Delete stock movement record
                $stockMovement->delete();

                // Delete the transaction
                $transaction->delete();
            }

            // Recalculate balances for all subsequent transactions (once for the batch)
            $this->transactionService->updateSubsequentBalances($firstTransactionDate);

            // Log batch deletion
            if ($restoreStock) {
                ActivityLog::log(
                    'delete',
                    sprintf(
                        'Deleted multi-item inventory sale batch (ID: %s) with %d items and restored stock',
                        $batchId,
                        count($restoredItems)
                    ),
                    'inventory_sales',
                    [
                        'batch_id' => $batchId,
                        'items_count' => count($restoredItems),
                        'restored_items' => $restoredItems,
                        'restore_stock' => true,
                    ]
                );
            } else {
                ActivityLog::log(
                    'delete',
                    sprintf(
                        'Permanently deleted multi-item inventory sale batch (ID: %s) with %d items without restoring stock',
                        $batchId,
                        count($itemNames)
                    ),
                    'inventory_sales',
                    [
                        'batch_id' => $batchId,
                        'items_count' => count($itemNames),
                        'items' => $itemNames,
                        'restore_stock' => false,
                    ]
                );
            }

            DB::commit();

            $itemsList = implode(', ', $itemNames);

            if ($restoreStock) {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Multi-item inventory sale deleted successfully! Restored stock for %d items: %s',
                        count($restoredItems),
                        $itemsList
                    ),
                    'restored_items' => $restoredItems
                ];
            } else {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Multi-item inventory sale permanently deleted without restoring stock for %d items: %s',
                        count($itemNames),
                        $itemsList
                    ),
                    'restored_items' => null
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an entire batch of inventory sale transactions (multi-item sale) - Legacy method.
     * 
     * @param string $batchId
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...]]
     * @throws Exception
     */
    public function deleteBatchInventorySale(string $batchId): array
    {
        DB::beginTransaction();

        try {
            // Get all transactions in the batch
            $batchTransactions = DailyTransaction::where('batch_id', $batchId)
                ->orderBy('id')
                ->get();

            if ($batchTransactions->isEmpty()) {
                throw new Exception('No transactions found with batch ID: ' . $batchId);
            }

            $restoredItems = [];
            $firstTransactionDate = $batchTransactions->first()->date;

            // Process each transaction in the batch
            foreach ($batchTransactions as $transaction) {
                // Validate this is an inventory sale transaction
                $this->validateInventorySale($transaction);

                // Find related stock movement
                $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                    ->where('reference_id', $transaction->id)
                    ->where('type', 'sale')
                    ->first();

                if (!$stockMovement) {
                    throw new Exception("Stock movement record not found for transaction ID: {$transaction->id}");
                }

                // Validate deletion is safe
                $this->validateSafeDeletion($transaction, $stockMovement);

                // Restore inventory stock
                $inventoryItem = $stockMovement->inventoryItem;
                $oldStock = (float) $inventoryItem->current_stock;
                $restoredQuantity = (float) $stockMovement->quantity;
                
                $inventoryItem->current_stock += $restoredQuantity;
                $inventoryItem->save();

                $newStock = $inventoryItem->current_stock;

                // Track restored item for response
                $restoredItems[] = [
                    'item_name' => $inventoryItem->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'restored_quantity' => $restoredQuantity,
                    'unit' => $inventoryItem->unit,
                ];

                // Delete stock movement record
                $stockMovement->delete();

                // Log the deletion
                $this->logInventorySaleDeletion($transaction, $inventoryItem, $oldStock, $newStock, $restoredQuantity);

                // Delete the transaction
                $transaction->delete();
            }

            // Recalculate balances for all subsequent transactions (once for the batch)
            $this->transactionService->updateSubsequentBalances($firstTransactionDate);

            // Log batch deletion
            ActivityLog::log(
                'delete',
                sprintf(
                    'Deleted multi-item inventory sale batch (ID: %s) with %d items',
                    $batchId,
                    count($restoredItems)
                ),
                'inventory_sales',
                [
                    'batch_id' => $batchId,
                    'items_count' => count($restoredItems),
                    'restored_items' => $restoredItems,
                ]
            );

            DB::commit();

            $itemsList = implode(', ', array_column($restoredItems, 'item_name'));

            return [
                'success' => true,
                'message' => sprintf(
                    'Multi-item inventory sale deleted successfully! Restored stock for %d items: %s',
                    count($restoredItems),
                    $itemsList
                ),
                'restored_items' => $restoredItems
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if a transaction is an inventory sale.
     * 
     * @param DailyTransaction $transaction
     * @return bool
     */
    public function isInventorySale(DailyTransaction $transaction): bool
    {
        // Check if this transaction has an associated stock movement of type 'sale'
        return StockMovement::where('reference_type', DailyTransaction::class)
            ->where('reference_id', $transaction->id)
            ->where('type', 'sale')
            ->exists();
    }

    /**
     * Validate that the transaction is an inventory sale.
     * 
     * @param DailyTransaction $transaction
     * @throws Exception
     */
    protected function validateInventorySale(DailyTransaction $transaction): void
    {
        // Check if category is "Inventory Item Sale"
        if ($transaction->category && $transaction->category->name !== 'Inventory Item Sale') {
            throw new Exception('This transaction is not an inventory sale.');
        }

        // Additional safety check: ensure it's an income transaction
        if ($transaction->income <= 0) {
            throw new Exception('Invalid inventory sale transaction: income should be greater than zero.');
        }

        // Check module is inventory
        if ($transaction->category && $transaction->category->module !== 'inventory') {
            throw new Exception('This transaction is not part of the inventory module.');
        }
    }

    /**
     * Validate that it's safe to delete this inventory sale.
     * 
     * @param DailyTransaction $transaction
     * @param StockMovement $stockMovement
     * @param bool $restoreStock
     * @throws Exception
     */
    protected function validateSafeDeletion(DailyTransaction $transaction, StockMovement $stockMovement, bool $restoreStock = true): void
    {
        // Check if transaction is locked (if locking feature is implemented)
        // Note: Add 'is_locked' column to daily_transactions table to enable this
        if (isset($transaction->is_locked) && $transaction->is_locked) {
            throw new Exception('Cannot delete locked transaction. Please unlock it first.');
        }

        // Validate inventory item still exists
        $inventoryItem = $stockMovement->inventoryItem;
        if (!$inventoryItem) {
            throw new Exception('Inventory item no longer exists.');
        }

        // Check if there are any subsequent stock movements for this item
        // that might have depended on the current stock level
        $hasSubsequentMovements = StockMovement::where('inventory_item_id', $inventoryItem->id)
            ->where('movement_date', '>', $stockMovement->movement_date)
            ->orWhere(function ($query) use ($stockMovement) {
                $query->where('movement_date', '=', $stockMovement->movement_date)
                    ->where('id', '>', $stockMovement->id);
            })
            ->exists();

        if ($hasSubsequentMovements) {
            // Check if restoring this stock would cause issues
            // For now, we allow it but log a warning
            // In stricter implementations, you might want to prevent deletion
            Log::warning(sprintf(
                'Deleting inventory sale for %s (ID: %d) which has subsequent stock movements. ' .
                'This may affect historical stock calculations.',
                $inventoryItem->name,
                $transaction->id
            ));
        }

        // Validate that restoring stock won't cause issues (only if restoring)
        // (This is generally safe, but we check for data integrity)
        if ($restoreStock) {
            $restoredStock = $inventoryItem->current_stock + $stockMovement->quantity;
            if ($restoredStock < 0) {
                throw new Exception('Cannot restore stock: would result in negative stock level.');
            }
        }
    }

    /**
     * Log the inventory sale deletion activity with stock restoration.
     * 
     * @param DailyTransaction $transaction
     * @param InventoryItem $inventoryItem
     * @param float $oldStock
     * @param float $newStock
     * @param float $restoredQuantity
     */
    protected function logInventorySaleDeletionWithRestore(
        DailyTransaction $transaction,
        InventoryItem $inventoryItem,
        float $oldStock,
        float $newStock,
        float $restoredQuantity
    ): void {
        $amount = (float) $transaction->income;
        
        ActivityLog::log(
            'delete',
            sprintf(
                'Deleted inventory sale: %s (Income: ₩%s) - Restored %s %s to %s',
                $transaction->description,
                number_format($amount, 0),
                number_format($restoredQuantity, 2),
                $inventoryItem->unit,
                $inventoryItem->name
            ),
            'inventory_sales',
            [
                'transaction_id' => $transaction->id,
                'transaction_date' => (string) $transaction->date,
                'amount' => $amount,
                'inventory_item_id' => $inventoryItem->id,
                'inventory_item_name' => $inventoryItem->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'restored_quantity' => $restoredQuantity,
                'unit' => $inventoryItem->unit,
                'batch_id' => $transaction->batch_id,
                'restore_stock' => true,
            ]
        );
    }

    /**
     * Log the inventory sale permanent deletion without stock restoration.
     * 
     * @param DailyTransaction $transaction
     * @param InventoryItem $inventoryItem
     * @param float $quantity
     */
    protected function logInventorySalePermanentDeletion(
        DailyTransaction $transaction,
        InventoryItem $inventoryItem,
        float $quantity
    ): void {
        $amount = (float) $transaction->income;
        
        ActivityLog::log(
            'delete',
            sprintf(
                'Permanently deleted inventory sale: %s (Income: ₩%s) - Stock NOT restored for %s (Qty: %s %s)',
                $transaction->description,
                number_format($amount, 0),
                $inventoryItem->name,
                number_format($quantity, 2),
                $inventoryItem->unit
            ),
            'inventory_sales',
            [
                'transaction_id' => $transaction->id,
                'transaction_date' => (string) $transaction->date,
                'amount' => $amount,
                'inventory_item_id' => $inventoryItem->id,
                'inventory_item_name' => $inventoryItem->name,
                'quantity' => $quantity,
                'unit' => $inventoryItem->unit,
                'batch_id' => $transaction->batch_id,
                'restore_stock' => false,
            ]
        );
    }

    /**
     * Legacy log method for backward compatibility.
     * 
     * @param DailyTransaction $transaction
     * @param InventoryItem $inventoryItem
     * @param float $oldStock
     * @param float $newStock
     * @param float $restoredQuantity
     */
    protected function logInventorySaleDeletion(
        DailyTransaction $transaction,
        InventoryItem $inventoryItem,
        float $oldStock,
        float $newStock,
        float $restoredQuantity
    ): void {
        $this->logInventorySaleDeletionWithRestore($transaction, $inventoryItem, $oldStock, $newStock, $restoredQuantity);
    }

    /**
     * Get information about an inventory sale deletion impact (preview).
     * Use this before actual deletion to show user what will happen.
     * 
     * @param DailyTransaction $transaction
     * @return array
     * @throws Exception
     */
    public function getDeletePreview(DailyTransaction $transaction): array
    {
        // Validate this is an inventory sale
        $this->validateInventorySale($transaction);

        // Check if part of batch
        $isBatch = false;
        $batchItems = [];
        
        if ($transaction->batch_id) {
            $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                ->with('category')
                ->get();
            
            if ($batchTransactions->count() > 1) {
                $isBatch = true;
                
                foreach ($batchTransactions as $batchTxn) {
                    $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                        ->where('reference_id', $batchTxn->id)
                        ->where('type', 'sale')
                        ->with('inventoryItem')
                        ->first();
                    
                    if ($stockMovement) {
                        $inventoryItem = $stockMovement->inventoryItem;
                        $batchItems[] = [
                            'transaction_id' => $batchTxn->id,
                            'item_name' => $inventoryItem->name,
                            'current_stock' => $inventoryItem->current_stock,
                            'quantity_to_restore' => $stockMovement->quantity,
                            'new_stock' => $inventoryItem->current_stock + $stockMovement->quantity,
                            'unit' => $inventoryItem->unit,
                            'sale_amount' => $batchTxn->income,
                        ];
                    }
                }
            }
        }

        // Single item
        if (!$isBatch) {
            $stockMovement = StockMovement::where('reference_type', DailyTransaction::class)
                ->where('reference_id', $transaction->id)
                ->where('type', 'sale')
                ->with('inventoryItem')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found.');
            }

            $inventoryItem = $stockMovement->inventoryItem;
            
            return [
                'is_batch' => false,
                'transaction_id' => $transaction->id,
                'transaction_date' => (string) $transaction->date,
                'transaction_amount' => (float) $transaction->income,
                'item_name' => $inventoryItem->name,
                'current_stock' => $inventoryItem->current_stock,
                'quantity_to_restore' => $stockMovement->quantity,
                'new_stock' => $inventoryItem->current_stock + $stockMovement->quantity,
                'unit' => $inventoryItem->unit,
                'has_subsequent_movements' => StockMovement::where('inventory_item_id', $inventoryItem->id)
                    ->where('movement_date', '>', $stockMovement->movement_date)
                    ->exists(),
            ];
        }

        // Batch
        return [
            'is_batch' => true,
            'batch_id' => $transaction->batch_id,
            'transaction_date' => (string) $transaction->date,
            'total_amount' => (float) $batchTransactions->sum('income'),
            'items' => $batchItems,
            'items_count' => count($batchItems),
        ];
    }
}
