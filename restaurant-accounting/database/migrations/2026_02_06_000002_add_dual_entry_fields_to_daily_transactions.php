<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_transactions', 'source')) {
                $table->string('source', 50)->nullable()->after('description')->comment('Transaction source: inventory, restaurant, customer, supplier, etc.');
                $table->index('source');
            }
            
            if (!Schema::hasColumn('daily_transactions', 'internal_reference_id')) {
                $table->string('internal_reference_id', 100)->nullable()->after('created_by')->comment('Shared ID for linking related transactions');
            }
            
            if (!Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->string('internal_reference_type', 100)->nullable()->after('internal_reference_id')->comment('Type of internal operation: inventory_internal_consumption, etc.');
            }
            
            if (!Schema::hasColumn('daily_transactions', 'internal_reference_id') || 
                !Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->index(['internal_reference_id', 'internal_reference_type'], 'dt_internal_ref_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('daily_transactions', 'source')) {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            }
            
            if (Schema::hasColumn('daily_transactions', 'internal_reference_id') || 
                Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->dropIndex('dt_internal_ref_idx');
            }
            
            if (Schema::hasColumn('daily_transactions', 'internal_reference_id')) {
                $table->dropColumn('internal_reference_id');
            }
            
            if (Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->dropColumn('internal_reference_type');
            }
        });
    }
};
