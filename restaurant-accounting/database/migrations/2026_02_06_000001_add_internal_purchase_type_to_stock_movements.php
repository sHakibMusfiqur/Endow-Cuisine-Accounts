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
        // For MySQL, we need to modify the enum by recreating it with all values
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('opening', 'in', 'out', 'adjustment', 'usage', 'sale', 'internal_purchase') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'internal_purchase' from enum
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('opening', 'in', 'out', 'adjustment', 'usage', 'sale') NOT NULL");
    }
};
