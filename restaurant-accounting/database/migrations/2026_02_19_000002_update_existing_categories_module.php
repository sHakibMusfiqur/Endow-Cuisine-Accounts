<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update existing categories to set appropriate module values.
     * Categories related to inventory should have module='inventory'.
     */
    public function up(): void
    {
        // Update inventory-related categories to use inventory module
        DB::table('categories')
            ->where(function($query) {
                $query->where('name', 'LIKE', '%inventory%')
                      ->orWhere('name', 'LIKE', '%Inventory%')
                      ->orWhere('name', 'LIKE', '%stock%')
                      ->orWhere('name', 'LIKE', '%Stock%');
            })
            ->update(['module' => 'inventory']);

        // Ensure all other categories remain as restaurant (default)
        DB::table('categories')
            ->whereNull('module')
            ->orWhere('module', '')
            ->update(['module' => 'restaurant']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as module has a default value
        // and this is just data cleanup
    }
};
