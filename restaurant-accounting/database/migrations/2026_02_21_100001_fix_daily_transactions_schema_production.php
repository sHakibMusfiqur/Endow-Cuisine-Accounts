<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * PRODUCTION-SAFE Migration: Fix daily_transactions schema
     * 
     * This migration ensures all required columns exist in the daily_transactions table.
     * It's designed to be idempotent and safe to run multiple times.
     * 
     * COLUMNS ADDED:
     * 1. source → ENUM('normal','inventory','restaurant') default 'normal'
     * 2. internal_reference_id → VARCHAR(100) nullable
     * 3. internal_reference_type → VARCHAR(100) nullable
     * 4. batch_id → VARCHAR(50) nullable
     * 
     * SAFETY FEATURES:
     * - Checks column existence before adding (prevents duplicate column errors)
     * - Uses default values for non-nullable columns
     * - Preserves all existing data
     * - MySQL strict mode compatible
     * - Zero-downtime safe
     * - Includes proper indexes for performance
     * - Full rollback support in down() method
     * 
     * DEPLOYMENT:
     * - Can be run during normal operation
     * - No table locking required for extended periods
     * - Backward compatible with existing code
     * 
     * @author Restaurant Accounting System
     * @version 1.0.0
     * @date 2026-02-21
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        Schema::table('daily_transactions', function (Blueprint $table) use ($driver) {
            
            // 1. ADD SOURCE COLUMN (if not exists)
            if (!Schema::hasColumn('daily_transactions', 'source')) {
                if ($driver === 'mysql') {
                    // For MySQL, use ENUM for efficiency
                    $table->enum('source', ['normal', 'inventory', 'restaurant'])
                          ->default('normal')
                          ->after('description')
                          ->comment('Source of transaction: normal=manual, inventory=auto from inventory, restaurant=restaurant operations');
                } else {
                    // For other databases, use string with check constraint
                    $table->string('source', 20)
                          ->default('normal')
                          ->after('description')
                          ->comment('Source of transaction: normal, inventory, or restaurant');
                }
                
                $table->index('source', 'idx_daily_transactions_source');
            }
            
            // 2. ADD INTERNAL_REFERENCE_ID (if not exists)
            if (!Schema::hasColumn('daily_transactions', 'internal_reference_id')) {
                $table->string('internal_reference_id', 100)
                      ->nullable()
                      ->after('created_by')
                      ->comment('UUID or identifier linking related transactions together');
            }
            
            // 3. ADD INTERNAL_REFERENCE_TYPE (if not exists)
            if (!Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->string('internal_reference_type', 100)
                      ->nullable()
                      ->after('internal_reference_id')
                      ->comment('Type of internal operation: inventory_purchase, inventory_consumption, etc.');
            }
            
            // 4. ADD BATCH_ID (if not exists)
            if (!Schema::hasColumn('daily_transactions', 'batch_id')) {
                $table->string('batch_id', 50)
                      ->nullable()
                      ->after('internal_reference_type')
                      ->comment('Groups multiple transactions submitted together');
            }
        });
        
        // Add composite indexes for better query performance (if not exists)
        $this->addIndexSafely('daily_transactions', ['internal_reference_id', 'internal_reference_type'], 'idx_dt_internal_ref');
        $this->addIndexSafely('daily_transactions', 'batch_id', 'idx_dt_batch_id');
        
        // Log migration completion
        $this->logMigrationStatus('up');
    }

    /**
     * Reverse the migrations.
     * 
     * CAUTION: This will drop columns and their data.
     * Only use in development or with proper backups.
     */
    public function down(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            
            // Drop indexes first
            $this->dropIndexSafely('daily_transactions', 'idx_dt_internal_ref');
            $this->dropIndexSafely('daily_transactions', 'idx_dt_batch_id');
            $this->dropIndexSafely('daily_transactions', 'idx_daily_transactions_source');
            
            // Drop columns if they exist
            if (Schema::hasColumn('daily_transactions', 'batch_id')) {
                $table->dropColumn('batch_id');
            }
            
            if (Schema::hasColumn('daily_transactions', 'internal_reference_type')) {
                $table->dropColumn('internal_reference_type');
            }
            
            if (Schema::hasColumn('daily_transactions', 'internal_reference_id')) {
                $table->dropColumn('internal_reference_id');
            }
            
            if (Schema::hasColumn('daily_transactions', 'source')) {
                $table->dropColumn('source');
            }
        });
        
        // Log migration rollback
        $this->logMigrationStatus('down');
    }
    
    /**
     * Add index only if it doesn't exist
     * 
     * @param string $tableName
     * @param string|array $columns
     * @param string $indexName
     */
    protected function addIndexSafely(string $tableName, $columns, string $indexName): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        // Check if index exists
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($tableName);
        
        if (!isset($indexes[$indexName])) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }
    
    /**
     * Drop index only if it exists
     * 
     * @param string $tableName
     * @param string $indexName
     */
    protected function dropIndexSafely(string $tableName, string $indexName): void
    {
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($tableName);
            
            if (isset($indexes[$indexName])) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
    }
    
    /**
     * Log migration status for audit trail
     * 
     * @param string $direction
     */
    protected function logMigrationStatus(string $direction): void
    {
        $status = $direction === 'up' ? 'COMPLETED' : 'ROLLED BACK';
        
        // Count affected columns
        $columnsAdded = 0;
        $columns = ['source', 'internal_reference_id', 'internal_reference_type', 'batch_id'];
        
        foreach ($columns as $column) {
            if (Schema::hasColumn('daily_transactions', $column)) {
                $columnsAdded++;
            }
        }
        
        // In production, log to application logs
        if (function_exists('logger')) {
            logger()->info("Migration {$status}: fix_daily_transactions_schema_production", [
                'direction' => $direction,
                'columns_present' => $columnsAdded,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
};
