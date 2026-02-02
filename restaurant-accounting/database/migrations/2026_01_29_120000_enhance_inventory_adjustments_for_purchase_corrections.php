<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Enhance inventory_adjustments table to support detailed purchase correction tracking.
     */
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            // Add columns for tracking financial corrections
            $table->decimal('old_expense_amount', 12, 2)->nullable()->after('difference');
            $table->decimal('corrected_expense_amount', 12, 2)->nullable()->after('old_expense_amount');
            $table->unsignedBigInteger('expense_transaction_id')->nullable()->after('corrected_expense_amount');
            $table->enum('correction_type', [
                'purchase_correction',
                'inventory_adjustment',
                'damage_spoilage',
                'manual_adjustment'
            ])->nullable()->after('reason');
            
            // Add index for better query performance
            $table->index('correction_type');
            $table->index('expense_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropIndex(['correction_type']);
            $table->dropIndex(['expense_transaction_id']);
            $table->dropColumn([
                'old_expense_amount',
                'corrected_expense_amount',
                'expense_transaction_id',
                'correction_type'
            ]);
        });
    }
};
