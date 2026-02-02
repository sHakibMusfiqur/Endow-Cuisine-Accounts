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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('old_quantity', 12, 2);
            $table->decimal('new_quantity', 12, 2);
            $table->decimal('difference', 12, 2); // Can be positive or negative
            $table->string('reason')->default('Manual Adjustment / Correction');
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('inventory_item_id');
            $table->index('adjusted_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
