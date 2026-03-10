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
 * Service for handling internal inventory consumption transaction deletions.
 * 
 * This service ensures proper restoration when internal consumption is deleted:
 * - Restores item stock levels
 * - Removes stock movement records
 * - Deletes both inventory and restaurant transaction records
 * - Recalculates running balances
 * - Maintains activity logs
 * - Handles both single and multi-item consumption
 * - Ensures profit reports update automatically
 */
class InternalConsumptionDeletionService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Delete an internal consumption transaction with option to restore stock.
     * 
     * @param DailyTransaction $transaction The transaction to delete (can be either inventory or restaurant side)
     * @param bool $restoreStock If true, restore inventory stock; if false, delete permanently without restoring
     * @param bool $skipBatchCheck If true, skip batch validation (used when deleting entire batch)
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...] or null]
     * @throws Exception
     */
    public function deleteInternalConsumptionWithOption(DailyTransaction $transaction, bool $restoreStock = true, bool $skipBatchCheck = false): array
    {
        DB::beginTransaction();

        try {
            // STEP 1: Validate this is an internal consumption transaction
            $this->validateInternalConsumption($transaction);

            // STEP 2: Check if this is part of a batch (multi-item consumption)
            if (!$skipBatchCheck && $transaction->batch_id) {
                // Get all transactions in this batch
                $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                    ->where('internal_reference_type', 'inventory_internal_consumption')
                    ->orderBy('id')
                    ->get();

                if ($batchTransactions->count() > 2) { // More than one pair means multi-item
                    // This is a multi-item consumption - delete the entire batch
                    return $this->deleteBatchInternalConsumptionWithOption($transaction->batch_id, $restoreStock);
                }
            }

            // STEP 3: Find the paired transactions (inventory income + restaurant expense)
            $internalReferenceId = $transaction->internal_reference_id;
            
            $pairedTransactions = DailyTransaction::where('internal_reference_id', $internalReferenceId)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->get();

            if ($pairedTransactions->count() !== 2) {
                throw new Exception('Invalid internal consumption: expected 2 paired transactions, found ' . $pairedTransactions->count());
            }

            // Identify inventory (income) and restaurant (expense) transactions
            $inventoryTransaction = $pairedTransactions->where('source', 'inventory')->first();
            $restaurantTransaction = $pairedTransactions->where('source', 'restaurant')->first();

            if (!$inventoryTransaction || !$restaurantTransaction) {
                throw new Exception('Could not identify inventory and restaurant transactions.');
            }

            // STEP 4: Find related stock movement
            $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                ->where('reference_id', $restaurantTransaction->id)
                ->where('type', 'internal_purchase')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found for this internal consumption transaction.');
            }

            // STEP 5: Validate deletion is safe
            $this->validateSafeDeletion($stockMovement, $restoreStock);

            $inventoryItem = $stockMovement->inventoryItem;
            if (!$inventoryItem) {
                throw new Exception('Inventory item no longer exists.');
            }

            $oldStock = (float) $inventoryItem->current_stock;
            $quantity = abs((float) $stockMovement->quantity);
            $restoredItems = null;

            // STEP 6: Conditionally restore inventory stock
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
                        'expense_amount' => (float) $restaurantTransaction->expense,
                    ]
                ];

                // STEP 7: Delete stock movement record
                $stockMovement->delete();

                // STEP 8: Log the deletion with restoration
                $this->logInternalConsumptionDeletionWithRestore(
                    $inventoryTransaction,
                    $restaurantTransaction,
                    $inventoryItem,
                    $oldStock,
                    $newStock,
                    $quantity
                );
            } else {
                // Permanent deletion without restoration
                $stockMovement->delete();

                // STEP 8: Log the permanent deletion
                $this->logInternalConsumptionPermanentDeletion(
                    $inventoryTransaction,
                    $restaurantTransaction,
                    $inventoryItem,
                    $quantity
                );
            }

            // STEP 9: Delete both transactions
            $date = $transaction->date;
            $inventoryTransaction->delete();
            $restaurantTransaction->delete();

            // STEP 10: Recalculate balances for all subsequent transactions
            $this->transactionService->updateSubsequentBalances($date);

            DB::commit();

            if ($restoreStock) {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Internal consumption deleted successfully! Stock restored from %s to %s %s for %s',
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
                        'Internal consumption permanently deleted without restoring stock for %s (Qty: %s %s)',
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
     * Delete an internal consumption transaction and restore inventory stock (legacy method).
     * 
     * @param DailyTransaction $transaction The transaction to delete (can be either inventory or restaurant side)
     * @param bool $skipBatchCheck If true, skip batch validation (used when deleting entire batch)
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...]]
     * @throws Exception
     */
    public function deleteInternalConsumption(DailyTransaction $transaction, bool $skipBatchCheck = false): array
    {
        DB::beginTransaction();

        try {
            // STEP 1: Validate this is an internal consumption transaction
            $this->validateInternalConsumption($transaction);

            // STEP 2: Check if this is part of a batch (multi-item consumption)
            if (!$skipBatchCheck && $transaction->batch_id) {
                // Get all transactions in this batch
                $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                    ->where('internal_reference_type', 'inventory_internal_consumption')
                    ->orderBy('id')
                    ->get();

                if ($batchTransactions->count() > 2) { // More than one pair means multi-item
                    // This is a multi-item consumption - delete the entire batch
                    return $this->deleteBatchInternalConsumption($transaction->batch_id);
                }
            }

            // STEP 3: Find the paired transactions (inventory income + restaurant expense)
            $internalReferenceId = $transaction->internal_reference_id;
            
            $pairedTransactions = DailyTransaction::where('internal_reference_id', $internalReferenceId)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->get();

            if ($pairedTransactions->count() !== 2) {
                throw new Exception('Invalid internal consumption: expected 2 paired transactions, found ' . $pairedTransactions->count());
            }

            // Identify inventory (income) and restaurant (expense) transactions
            $inventoryTransaction = $pairedTransactions->where('source', 'inventory')->first();
            $restaurantTransaction = $pairedTransactions->where('source', 'restaurant')->first();

            if (!$inventoryTransaction || !$restaurantTransaction) {
                throw new Exception('Could not identify inventory and restaurant transactions.');
            }

            // STEP 4: Find related stock movement
            $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                ->where('reference_id', $restaurantTransaction->id)
                ->where('type', 'internal_purchase')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found for this internal consumption transaction.');
            }

            // STEP 5: Validate deletion is safe
            $this->validateSafeDeletion($stockMovement);

            // STEP 6: Restore inventory stock
            $inventoryItem = $stockMovement->inventoryItem;
            if (!$inventoryItem) {
                throw new Exception('Inventory item no longer exists.');
            }

            $oldStock = (float) $inventoryItem->current_stock;
            $restoredQuantity = abs((float) $stockMovement->quantity); // Make positive
            
            $inventoryItem->current_stock += $restoredQuantity;
            $inventoryItem->save();

            $newStock = $inventoryItem->current_stock;

            // STEP 7: Delete stock movement record
            $stockMovement->delete();

            // STEP 8: Log the deletion before deleting transactions
            $this->logInternalConsumptionDeletion(
                $inventoryTransaction,
                $restaurantTransaction,
                $inventoryItem,
                $oldStock,
                $newStock,
                $restoredQuantity
            );

            // STEP 9: Delete both transactions
            $date = $transaction->date;
            $inventoryTransaction->delete();
            $restaurantTransaction->delete();

            // STEP 10: Recalculate balances for all subsequent transactions
            $this->transactionService->updateSubsequentBalances($date);

            DB::commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'Internal consumption deleted successfully! Stock restored from %s to %s %s for %s',
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
                        'expense_amount' => (float) $restaurantTransaction->expense,
                    ]
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an entire batch consumption transactions with restore option.
     * 
     * @param string $batchId
     * @param bool $restoreStock
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...] or null]
     * @throws Exception
     */
    public function deleteBatchInternalConsumptionWithOption(string $batchId, bool $restoreStock = true): array
    {
        DB::beginTransaction();

        try {
            // Get all transactions in the batch
            $batchTransactions = DailyTransaction::where('batch_id', $batchId)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->orderBy('id')
                ->get();

            if ($batchTransactions->isEmpty()) {
                throw new Exception('No internal consumption transactions found with batch ID: ' . $batchId);
            }

            // Group transactions by internal_reference_id (each pair shares same reference)
            $transactionPairs = $batchTransactions->groupBy('internal_reference_id');

            $restoredItems = [];
            $itemNames = [];
            $firstTransactionDate = $batchTransactions->first()->date;

            // Process each pair of transactions
            foreach ($transactionPairs as $referenceId => $pairedTxns) {
                if ($pairedTxns->count() !== 2) {
                    throw new Exception("Invalid transaction pair for reference ID: {$referenceId}");
                }

                // Identify inventory (income) and restaurant (expense) transactions
                $inventoryTransaction = $pairedTxns->where('source', 'inventory')->first();
                $restaurantTransaction = $pairedTxns->where('source', 'restaurant')->first();

                if (!$inventoryTransaction || !$restaurantTransaction) {
                    throw new Exception("Could not identify inventory and restaurant transactions for reference ID: {$referenceId}");
                }

                // Find related stock movement
                $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                    ->where('reference_id', $restaurantTransaction->id)
                    ->where('type', 'internal_purchase')
                    ->first();

                if (!$stockMovement) {
                    throw new Exception("Stock movement record not found for transaction ID: {$restaurantTransaction->id}");
                }

                // Validate deletion is safe
                $this->validateSafeDeletion($stockMovement, $restoreStock);

                // Restore inventory stock
                $inventoryItem = $stockMovement->inventoryItem;
                if (!$inventoryItem) {
                    throw new Exception('Inventory item no longer exists.');
                }

                $oldStock = (float) $inventoryItem->current_stock;
                $quantity = abs((float) $stockMovement->quantity);
                $itemNames[] = $inventoryItem->name;

                if ($restoreStock) {
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
                        'expense_amount' => (float) $restaurantTransaction->expense,
                    ];

                    // Log the deletion
                    $this->logInternalConsumptionDeletionWithRestore(
                        $inventoryTransaction,
                        $restaurantTransaction,
                        $inventoryItem,
                        $oldStock,
                        $newStock,
                        $quantity
                    );
                } else {
                    // Permanent deletion without restoration
                    $this->logInternalConsumptionPermanentDeletion(
                        $inventoryTransaction,
                        $restaurantTransaction,
                        $inventoryItem,
                        $quantity
                    );
                }

                // Delete stock movement record
                $stockMovement->delete();

                // Delete both transactions
                $inventoryTransaction->delete();
                $restaurantTransaction->delete();
            }

            // Recalculate balances for all subsequent transactions (once for the batch)
            $this->transactionService->updateSubsequentBalances($firstTransactionDate);

            // Log batch deletion
            if ($restoreStock) {
                ActivityLog::log(
                    'delete',
                    sprintf(
                        'Deleted multi-item internal consumption batch (ID: %s) with %d items and restored stock',
                        $batchId,
                        count($restoredItems)
                    ),
                    'internal_consumption',
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
                        'Permanently deleted multi-item internal consumption batch (ID: %s) with %d items without restoring stock',
                        $batchId,
                        count($itemNames)
                    ),
                    'internal_consumption',
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
                        'Multi-item internal consumption deleted successfully! Restored stock for %d items: %s',
                        count($restoredItems),
                        $itemsList
                    ),
                    'restored_items' => $restoredItems
                ];
            } else {
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Multi-item internal consumption permanently deleted without restoring stock for %d items: %s',
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
     * Delete an entire batch of internal consumption transactions (multi-item consumption) - Legacy method.
     * 
     * @param string $batchId
     * @return array ['success' => true, 'message' => '...', 'restored_items' => [...]]
     * @throws Exception
     */
    public function deleteBatchInternalConsumption(string $batchId): array
    {
        DB::beginTransaction();

        try {
            // Get all transactions in the batch
            $batchTransactions = DailyTransaction::where('batch_id', $batchId)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->orderBy('id')
                ->get();

            if ($batchTransactions->isEmpty()) {
                throw new Exception('No internal consumption transactions found with batch ID: ' . $batchId);
            }

            // Group transactions by internal_reference_id (each pair shares same reference)
            $transactionPairs = $batchTransactions->groupBy('internal_reference_id');

            $restoredItems = [];
            $firstTransactionDate = $batchTransactions->first()->date;

            // Process each pair of transactions
            foreach ($transactionPairs as $referenceId => $pairedTxns) {
                if ($pairedTxns->count() !== 2) {
                    throw new Exception("Invalid transaction pair for reference ID: {$referenceId}");
                }

                // Identify inventory (income) and restaurant (expense) transactions
                $inventoryTransaction = $pairedTxns->where('source', 'inventory')->first();
                $restaurantTransaction = $pairedTxns->where('source', 'restaurant')->first();

                if (!$inventoryTransaction || !$restaurantTransaction) {
                    throw new Exception("Could not identify inventory and restaurant transactions for reference ID: {$referenceId}");
                }

                // Find related stock movement
                $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                    ->where('reference_id', $restaurantTransaction->id)
                    ->where('type', 'internal_purchase')
                    ->first();

                if (!$stockMovement) {
                    throw new Exception("Stock movement record not found for transaction ID: {$restaurantTransaction->id}");
                }

                // Validate deletion is safe
                $this->validateSafeDeletion($stockMovement);

                // Restore inventory stock
                $inventoryItem = $stockMovement->inventoryItem;
                if (!$inventoryItem) {
                    throw new Exception('Inventory item no longer exists.');
                }

                $oldStock = (float) $inventoryItem->current_stock;
                $restoredQuantity = abs((float) $stockMovement->quantity);
                
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
                    'expense_amount' => (float) $restaurantTransaction->expense,
                ];

                // Delete stock movement record
                $stockMovement->delete();

                // Log the deletion
                $this->logInternalConsumptionDeletion(
                    $inventoryTransaction,
                    $restaurantTransaction,
                    $inventoryItem,
                    $oldStock,
                    $newStock,
                    $restoredQuantity
                );

                // Delete both transactions
                $inventoryTransaction->delete();
                $restaurantTransaction->delete();
            }

            // Recalculate balances for all subsequent transactions (once for the batch)
            $this->transactionService->updateSubsequentBalances($firstTransactionDate);

            // Log batch deletion
            ActivityLog::log(
                'delete',
                sprintf(
                    'Deleted multi-item internal consumption batch (ID: %s) with %d items',
                    $batchId,
                    count($restoredItems)
                ),
                'internal_consumption',
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
                    'Multi-item internal consumption deleted successfully! Restored stock for %d items: %s',
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
     * Check if a transaction is an internal consumption.
     * 
     * @param DailyTransaction $transaction
     * @return bool
     */
    public function isInternalConsumption(DailyTransaction $transaction): bool
    {
        return $transaction->internal_reference_type === 'inventory_internal_consumption';
    }

    /**
     * Validate that the transaction is an internal consumption.
     * 
     * @param DailyTransaction $transaction
     * @throws Exception
     */
    protected function validateInternalConsumption(DailyTransaction $transaction): void
    {
        // Check internal_reference_type
        if ($transaction->internal_reference_type !== 'inventory_internal_consumption') {
            throw new Exception('This transaction is not an internal consumption transaction.');
        }

        // Check that source is either 'inventory' or 'restaurant'
        if (!in_array($transaction->source, ['inventory', 'restaurant'])) {
            throw new Exception('Invalid source for internal consumption transaction.');
        }

        // Validate internal_reference_id exists
        if (!$transaction->internal_reference_id) {
            throw new Exception('Internal reference ID is missing.');
        }
    }

    /**
     * Validate that it's safe to delete this internal consumption.
     * 
     * @param StockMovement $stockMovement
     * @param bool $restoreStock
     * @throws Exception
     */
    protected function validateSafeDeletion(StockMovement $stockMovement, bool $restoreStock = true): void
    {
        // Validate inventory item still exists
        $inventoryItem = $stockMovement->inventoryItem;
        if (!$inventoryItem) {
            throw new Exception('Inventory item no longer exists.');
        }

        // Check if there are any subsequent stock movements for this item
        $hasSubsequentMovements = StockMovement::where('inventory_item_id', $inventoryItem->id)
            ->where(function ($query) use ($stockMovement) {
                $query->where('movement_date', '>', $stockMovement->movement_date)
                    ->orWhere(function ($q) use ($stockMovement) {
                        $q->where('movement_date', '=', $stockMovement->movement_date)
                          ->where('id', '>', $stockMovement->id);
                    });
            })
            ->exists();

        if ($hasSubsequentMovements) {
            Log::warning(sprintf(
                'Deleting internal consumption for %s (Movement ID: %d) which has subsequent stock movements. ' .
                'This may affect historical stock calculations.',
                $inventoryItem->name,
                $stockMovement->id
            ));
        }

        // Validate that restoring stock won't cause negative stock (only if restoring)
        if ($restoreStock) {
            $restoredStock = (float) $inventoryItem->current_stock + abs((float) $stockMovement->quantity);
            if ($restoredStock < 0) {
                throw new Exception('Cannot restore stock: would result in negative stock level.');
            }
        }
    }

    /**
     * Log the internal consumption deletion activity with stock restoration.
     * 
     * @param DailyTransaction $inventoryTransaction
     * @param DailyTransaction $restaurantTransaction
     * @param InventoryItem $inventoryItem
     * @param float $oldStock
     * @param float $newStock
     * @param float $restoredQuantity
     */
    protected function logInternalConsumptionDeletionWithRestore(
        DailyTransaction $inventoryTransaction,
        DailyTransaction $restaurantTransaction,
        InventoryItem $inventoryItem,
        float $oldStock,
        float $newStock,
        float $restoredQuantity
    ): void {
        $expenseAmount = (float) $restaurantTransaction->expense;
        
        ActivityLog::log(
            'delete',
            sprintf(
                'Deleted internal consumption: %s (Expense: ₩%s) - Restored %s %s to %s',
                $restaurantTransaction->description,
                number_format($expenseAmount, 0),
                number_format($restoredQuantity, 2),
                $inventoryItem->unit,
                $inventoryItem->name
            ),
            'internal_consumption',
            [
                'inventory_transaction_id' => $inventoryTransaction->id,
                'restaurant_transaction_id' => $restaurantTransaction->id,
                'transaction_date' => (string) $restaurantTransaction->date,
                'expense_amount' => $expenseAmount,
                'income_amount' => (float) $inventoryTransaction->income,
                'inventory_item_id' => $inventoryItem->id,
                'inventory_item_name' => $inventoryItem->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'restored_quantity' => $restoredQuantity,
                'unit' => $inventoryItem->unit,
                'batch_id' => $restaurantTransaction->batch_id,
                'internal_reference_id' => $restaurantTransaction->internal_reference_id,
                'restore_stock' => true,
            ]
        );
    }

    /**
     * Log the internal consumption permanent deletion without stock restoration.
     * 
     * @param DailyTransaction $inventoryTransaction
     * @param DailyTransaction $restaurantTransaction
     * @param InventoryItem $inventoryItem
     * @param float $quantity
     */
    protected function logInternalConsumptionPermanentDeletion(
        DailyTransaction $inventoryTransaction,
        DailyTransaction $restaurantTransaction,
        InventoryItem $inventoryItem,
        float $quantity
    ): void {
        $expenseAmount = (float) $restaurantTransaction->expense;
        
        ActivityLog::log(
            'delete',
            sprintf(
                'Permanently deleted internal consumption: %s (Expense: ₩%s) - Stock NOT restored for %s (Qty: %s %s)',
                $restaurantTransaction->description,
                number_format($expenseAmount, 0),
                $inventoryItem->name,
                number_format($quantity, 2),
                $inventoryItem->unit
            ),
            'internal_consumption',
            [
                'inventory_transaction_id' => $inventoryTransaction->id,
                'restaurant_transaction_id' => $restaurantTransaction->id,
                'transaction_date' => (string) $restaurantTransaction->date,
                'expense_amount' => $expenseAmount,
                'income_amount' => (float) $inventoryTransaction->income,
                'inventory_item_id' => $inventoryItem->id,
                'inventory_item_name' => $inventoryItem->name,
                'quantity' => $quantity,
                'unit' => $inventoryItem->unit,
                'batch_id' => $restaurantTransaction->batch_id,
                'internal_reference_id' => $restaurantTransaction->internal_reference_id,
                'restore_stock' => false,
            ]
        );
    }

    /**
     * Legacy log method for backward compatibility.
     * 
     * @param DailyTransaction $inventoryTransaction
     * @param DailyTransaction $restaurantTransaction
     * @param InventoryItem $inventoryItem
     * @param float $oldStock
     * @param float $newStock
     * @param float $restoredQuantity
     */
    protected function logInternalConsumptionDeletion(
        DailyTransaction $inventoryTransaction,
        DailyTransaction $restaurantTransaction,
        InventoryItem $inventoryItem,
        float $oldStock,
        float $newStock,
        float $restoredQuantity
    ): void {
        $this->logInternalConsumptionDeletionWithRestore(
            $inventoryTransaction,
            $restaurantTransaction,
            $inventoryItem,
            $oldStock,
            $newStock,
            $restoredQuantity
        );
    }

    /**
     * Get information about an internal consumption deletion impact (preview).
     * Use this before actual deletion to show user what will happen.
     * 
     * @param DailyTransaction $transaction
     * @return array
     * @throws Exception
     */
    public function getDeletePreview(DailyTransaction $transaction): array
    {
        // Validate this is an internal consumption
        $this->validateInternalConsumption($transaction);

        // Check if part of batch
        $isBatch = false;
        $batchItems = [];
        
        if ($transaction->batch_id) {
            $batchTransactions = DailyTransaction::where('batch_id', $transaction->batch_id)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->get();
            
            if ($batchTransactions->count() > 2) { // More than one pair
                $isBatch = true;
                
                // Group by internal_reference_id
                $transactionPairs = $batchTransactions->groupBy('internal_reference_id');
                
                foreach ($transactionPairs as $referenceId => $pairedTxns) {
                    $inventoryTransaction = $pairedTxns->where('source', 'inventory')->first();
                    $restaurantTransaction = $pairedTxns->where('source', 'restaurant')->first();
                    
                    if ($restaurantTransaction) {
                        $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                            ->where('reference_id', $restaurantTransaction->id)
                            ->where('type', 'internal_purchase')
                            ->with('inventoryItem')
                            ->first();
                        
                        if ($stockMovement) {
                            $inventoryItem = $stockMovement->inventoryItem;
                            $batchItems[] = [
                                'inventory_transaction_id' => $inventoryTransaction->id,
                                'restaurant_transaction_id' => $restaurantTransaction->id,
                                'item_name' => $inventoryItem->name,
                                'current_stock' => $inventoryItem->current_stock,
                                'quantity_to_restore' => abs($stockMovement->quantity),
                                'new_stock' => $inventoryItem->current_stock + abs($stockMovement->quantity),
                                'unit' => $inventoryItem->unit,
                                'expense_amount' => $restaurantTransaction->expense,
                            ];
                        }
                    }
                }
            }
        }

        // Single item
        if (!$isBatch) {
            // Find paired transactions
            $pairedTransactions = DailyTransaction::where('internal_reference_id', $transaction->internal_reference_id)
                ->where('internal_reference_type', 'inventory_internal_consumption')
                ->get();

            $inventoryTransaction = $pairedTransactions->where('source', 'inventory')->first();
            $restaurantTransaction = $pairedTransactions->where('source', 'restaurant')->first();

            if (!$restaurantTransaction) {
                throw new Exception('Restaurant transaction not found.');
            }

            $stockMovement = StockMovement::where('reference_type', 'App\Models\DailyTransaction')
                ->where('reference_id', $restaurantTransaction->id)
                ->where('type', 'internal_purchase')
                ->with('inventoryItem')
                ->first();

            if (!$stockMovement) {
                throw new Exception('Stock movement record not found.');
            }

            $inventoryItem = $stockMovement->inventoryItem;
            
            return [
                'is_batch' => false,
                'inventory_transaction_id' => $inventoryTransaction->id,
                'restaurant_transaction_id' => $restaurantTransaction->id,
                'transaction_date' => (string) $transaction->date,
                'expense_amount' => (float) $restaurantTransaction->expense,
                'income_amount' => (float) $inventoryTransaction->income,
                'item_name' => $inventoryItem->name,
                'current_stock' => $inventoryItem->current_stock,
                'quantity_to_restore' => abs($stockMovement->quantity),
                'new_stock' => $inventoryItem->current_stock + abs($stockMovement->quantity),
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
            'total_expense' => (float) DailyTransaction::where('batch_id', $transaction->batch_id)
                ->where('source', 'restaurant')
                ->sum('expense'),
            'items' => $batchItems,
            'items_count' => count($batchItems),
        ];
    }
}
