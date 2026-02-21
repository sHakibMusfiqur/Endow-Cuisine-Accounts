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
     * This migration updates the 'type' ENUM column in stock_movements table
     * to include all necessary transaction types.
     * 
     * PRODUCTION SAFETY:
     * - Non-destructive: Preserves all existing values
     * - Adds new values: 'internal_purchase', 'internal_consumption'
     * - No data loss: All existing rows remain intact
     * - Compatible with MySQL strict mode
     * - Safe for zero-downtime deployment
     * 
     * ENUM VALUES:
     * - opening: Initial stock setup
     * - in: Regular inventory purchase
     * - out: Stock out (waste/damage)
     * - adjustment: Manual inventory correction
     * - usage: Auto-deduction from sales
     * - sale: Direct inventory sale to customer
     * - internal_purchase: Internal consumption with cost allocation
     * - internal_consumption: Internal usage tracking
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: ALTER COLUMN to redefine ENUM with all values
            // This is SAFE: existing data is preserved as long as old values are included
            DB::statement("
                ALTER TABLE stock_movements 
                MODIFY COLUMN type ENUM(
                    'opening',
                    'in',
                    'out',
                    'adjustment',
                    'usage',
                    'sale',
                    'internal_purchase',
                    'internal_consumption'
                ) NOT NULL
                COMMENT 'Type of stock movement'
            ");
            
            // Verify no data was truncated (safety check)
            $truncatedCount = DB::table('stock_movements')
                ->whereNull('type')
                ->orWhere('type', '')
                ->count();
            
            if ($truncatedCount > 0) {
                throw new \Exception("Migration failed: {$truncatedCount} rows have invalid type values. Rollback recommended.");
            }
            
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Add new enum values
            // Note: PostgreSQL doesn't allow removing enum values easily
            DB::statement("ALTER TYPE stock_movement_type ADD VALUE IF NOT EXISTS 'internal_purchase'");
            DB::statement("ALTER TYPE stock_movement_type ADD VALUE IF NOT EXISTS 'internal_consumption'");
            
        } else {
            // SQLite / Other: Convert to VARCHAR (more flexible)
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 30)->change();
            });
        }
        
        // Optional: Add index for new types if filtering performance is important
        // DB::statement('CREATE INDEX idx_stock_movements_internal_types ON stock_movements(type) WHERE type IN ("internal_purchase", "internal_consumption")');
    }

    /**
     * Reverse the migrations.
     * 
     * ROLLBACK SAFETY:
     * - Checks if any data uses the new enum values before removing them
     * - Prevents data loss by blocking rollback if new types are in use
     * - Can safely revert if no data uses new types
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // SAFETY CHECK: Prevent rollback if data exists with new types
            $internalPurchaseCount = DB::table('stock_movements')
                ->where('type', 'internal_purchase')
                ->count();
            
            $internalConsumptionCount = DB::table('stock_movements')
                ->where('type', 'internal_consumption')
                ->count();
            
            if ($internalPurchaseCount > 0 || $internalConsumptionCount > 0) {
                throw new \Exception(
                    "Cannot rollback: {$internalPurchaseCount} 'internal_purchase' and " .
                    "{$internalConsumptionCount} 'internal_consumption' records exist. " .
                    "Data loss would occur. Manual intervention required."
                );
            }
            
            // Safe to rollback: restore original enum
            DB::statement("
                ALTER TABLE stock_movements 
                MODIFY COLUMN type ENUM(
                    'opening',
                    'in',
                    'out',
                    'adjustment',
                    'usage',
                    'sale'
                ) NOT NULL
            ");
            
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Cannot easily remove enum values
            // Would require creating new type and migrating data
            throw new \Exception(
                "PostgreSQL enum values cannot be removed. " .
                "Manual schema update required for rollback."
            );
            
        } else {
            // SQLite: No action needed (already VARCHAR)
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 30)->change();
            });
        }
    }
};
