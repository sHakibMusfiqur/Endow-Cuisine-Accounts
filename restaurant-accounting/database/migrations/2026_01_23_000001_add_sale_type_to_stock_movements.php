<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if database driver supports ENUM modification
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // For MySQL, we need to recreate the ENUM
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('opening', 'in', 'out', 'adjustment', 'usage', 'sale') NOT NULL");
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, add the new value to the enum type
            DB::statement("ALTER TYPE stock_movement_type ADD VALUE IF NOT EXISTS 'sale'");
        } else {
            // For other drivers (SQLite), drop and recreate the constraint
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 20)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Remove 'sale' from the ENUM
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('opening', 'in', 'out', 'adjustment', 'usage') NOT NULL");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL doesn't support removing enum values easily
            // Would need to create a new type and migrate data
        } else {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 20)->change();
            });
        }
    }
};
