<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds a 'source' ENUM column to daily_transactions table.
     * The column tracks the origin of the transaction:
     * - 'normal': Regular manual transactions
     * - 'inventory': Transactions generated from inventory operations
     * - 'restaurant': Transactions from restaurant-specific operations
     * 
     * PRODUCTION SAFETY:
     * - Uses default value 'normal' so existing rows are automatically populated
     * - Non-breaking: All existing data remains intact
     * - Indexed for query performance
     */
    public function up(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            // Add source column with ENUM type
            $table->enum('source', ['normal', 'inventory', 'restaurant'])
                  ->default('normal')
                  ->after('description')
                  ->comment('Source of the transaction: normal, inventory, or restaurant');
            
            // Add index for better query performance when filtering by source
            $table->index('source');
        });
        
        // Optional: Update existing records if you need different logic
        // Example: Mark existing inventory-related transactions
        // DB::table('daily_transactions')
        //     ->whereExists(function ($query) {
        //         $query->select(DB::raw(1))
        //               ->from('inventory_adjustments')
        //               ->whereColumn('inventory_adjustments.expense_transaction_id', 'daily_transactions.id');
        //     })
        //     ->update(['source' => 'inventory']);
    }

    /**
     * Reverse the migrations.
     * 
     * ROLLBACK SAFETY:
     * - Simply drops the column
     * - No data loss for other columns
     * - Can be re-run if needed
     */
    public function down(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            // Drop index first, then column
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
