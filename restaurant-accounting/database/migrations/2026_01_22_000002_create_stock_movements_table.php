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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['opening', 'in', 'out', 'adjustment', 'usage', 'sale']); // opening=initial stock, in=purchase, out=waste, adjustment=correction, usage=auto from sales, sale=direct inventory sale
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2)->nullable(); // Cost at time of movement
            $table->decimal('total_cost', 10, 2)->nullable(); // quantity * unit_cost
            $table->decimal('balance_after', 10, 2); // Stock level after this movement
            $table->string('reference_type')->nullable(); // 'transaction', 'manual', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related transaction if applicable
            $table->text('notes')->nullable();
            $table->date('movement_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['inventory_item_id', 'movement_date']);
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
