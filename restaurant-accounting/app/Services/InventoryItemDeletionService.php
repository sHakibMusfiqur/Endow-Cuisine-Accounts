<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\DailyTransaction;
use App\Models\StockMovement;
use App\Models\InventoryAdjustment;
use App\Models\ItemUsageRecipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for comprehensive deletion of inventory items.
 * 
 * CRITICAL BUSINESS RULE:
 * When an inventory item is deleted, ALL related data must be removed:
 * - Stock movements (purchases, sales, usage, adjustments)
 * - Inventory adjustments (corrections, damage/spoilage)
 * - Usage recipes
 * - Related financial transactions
 * 
 * After deletion, all financial summaries and reports must automatically
 * recalculate to reflect the changes. No stale data, no orphans.
 */
class InventoryItemDeletionService
{
    /**
     * Completely delete an inventory item and all related data.
     * 
     * This method ensures:
     * 1. All related records are identified and deleted
     * 2. Financial transactions linked to this item are removed
     * 3. Running balances are recalculated after transaction deletions
     * 4. Everything happens atomically (all or nothing)
     * 
     * @param InventoryItem $item The item to delete
     * @return array Summary of deletion operations
     * @throws Exception If deletion fails
     */
    public function deleteInventoryItem(InventoryItem $item): array
    {
        return DB::transaction(function () use ($item) {
            $summary = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'item_sku' => $item->sku,
                'deleted_records' => []
            ];

            try {
                // STEP 1: Identify and delete all financial transactions related to this item
                $deletedTransactions = $this->deleteRelatedTransactions($item);
                $summary['deleted_records']['transactions'] = $deletedTransactions;

                // STEP 2: Delete all usage recipes (no foreign key cascade needed)
                $deletedRecipes = ItemUsageRecipe::where('inventory_item_id', $item->id)->count();
                ItemUsageRecipe::where('inventory_item_id', $item->id)->delete();
                $summary['deleted_records']['usage_recipes'] = $deletedRecipes;

                // STEP 3: Delete all inventory adjustments
                // Note: Foreign key cascade is configured, but we delete explicitly for clarity
                $deletedAdjustments = InventoryAdjustment::where('inventory_item_id', $item->id)->count();
                InventoryAdjustment::where('inventory_item_id', $item->id)->delete();
                $summary['deleted_records']['adjustments'] = $deletedAdjustments;

                // STEP 4: Delete all stock movements
                // Note: Foreign key cascade is configured, but we delete explicitly for clarity
                $deletedMovements = StockMovement::where('inventory_item_id', $item->id)->count();
                StockMovement::where('inventory_item_id', $item->id)->delete();
                $summary['deleted_records']['stock_movements'] = $deletedMovements;

                // STEP 5: Delete the inventory item itself
                $item->delete();
                $summary['deleted_records']['inventory_item'] = 1;

                // STEP 6: Recalculate all transaction balances from the earliest affected date
                if ($deletedTransactions > 0) {
                    $this->recalculateAllBalances();
                }

                // Log the successful deletion
                Log::info('Inventory item deleted completely', $summary);

                return $summary;

            } catch (Exception $e) {
                Log::error('Failed to delete inventory item', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw new Exception("Failed to delete inventory item: {$e->getMessage()}");
            }
        });
    }

    /**
     * Delete all financial transactions related to an inventory item.
     * 
     * This includes:
     * - Opening stock purchase transactions (description contains "Opening Stock: {item_name}")
     * - Inventory sale transactions (via stock_movements with reference_type = DailyTransaction)
     * - Purchase transactions that created stock movements for this item
     * 
     * @param InventoryItem $item
     * @return int Number of transactions deleted
     */
    protected function deleteRelatedTransactions(InventoryItem $item): int
    {
        $transactionIds = collect();

        // Find opening stock transactions by description pattern
        $openingStockTransactions = DailyTransaction::where('description', 'LIKE', "Opening Stock: {$item->name}%")
            ->pluck('id');
        $transactionIds = $transactionIds->merge($openingStockTransactions);

        // Find transactions linked via stock movements
        // These are inventory sales and purchases that created stock movements
        $linkedTransactions = StockMovement::where('inventory_item_id', $item->id)
            ->where('reference_type', DailyTransaction::class)
            ->pluck('reference_id')
            ->filter(); // Remove nulls
        $transactionIds = $transactionIds->merge($linkedTransactions);

        // Find transactions where this item was involved in automatic inventory usage
        // These are food sales that auto-deducted this item's stock via usage recipes
        $usageTransactions = StockMovement::where('inventory_item_id', $item->id)
            ->where('type', 'usage')
            ->where('reference_type', 'transaction')
            ->pluck('reference_id')
            ->filter();
        $transactionIds = $transactionIds->merge($usageTransactions);

        // Get unique transaction IDs
        $transactionIds = $transactionIds->unique();

        // Delete all identified transactions
        if ($transactionIds->isNotEmpty()) {
            DailyTransaction::whereIn('id', $transactionIds)->delete();
        }

        return $transactionIds->count();
    }

    /**
     * Recalculate running balances for all transactions.
     * 
     * This is necessary after deleting transactions to ensure
     * the running balance column is accurate for all remaining transactions.
     * 
     * The balance is calculated sequentially:
     * balance = previous_balance + income - expense
     * 
     * @return void
     */
    protected function recalculateAllBalances(): void
    {
        // Get all transactions ordered by date and ID
        $transactions = DailyTransaction::orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;

        foreach ($transactions as $transaction) {
            // Calculate new balance
            $runningBalance = $runningBalance + $transaction->income - $transaction->expense;
            
            // Update balance if it changed
            if ($transaction->balance != $runningBalance) {
                $transaction->balance = $runningBalance;
                $transaction->save();
            }
        }

        Log::info('Recalculated all transaction balances', [
            'total_transactions' => $transactions->count(),
            'final_balance' => $runningBalance
        ]);
    }

    /**
     * Check if an inventory item can be safely deleted.
     * 
     * This is informational only - deletion is allowed for all items
     * regardless of their state, but this provides useful info for logging.
     * 
     * @param InventoryItem $item
     * @return array Information about related records
     */
    public function getDeletionImpact(InventoryItem $item): array
    {
        return [
            'item_name' => $item->name,
            'current_stock' => $item->current_stock,
            'stock_movements_count' => $item->stockMovements()->count(),
            'adjustments_count' => $item->adjustments()->count(),
            'usage_recipes_count' => $item->usageRecipes()->count(),
            'is_active' => $item->is_active,
        ];
    }
}
