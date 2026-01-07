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
            // Add currency support columns
            $table->foreignId('currency_id')->after('payment_method_id')
                ->constrained('currencies')
                ->onDelete('restrict'); // Prevent deletion of currencies in use
            
            // Store the original amount in the selected currency
            $table->decimal('amount_original', 15, 2)->after('expense')->default(0);
            
            // Store the converted amount in base currency (KRW)
            $table->decimal('amount_base', 15, 2)->after('amount_original')->default(0);
            
            // Store the exchange rate at the time of transaction (for historical accuracy)
            $table->decimal('exchange_rate_snapshot', 15, 6)->after('amount_base')->default(1.000000);
            
            // Add index for better query performance
            $table->index('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'amount_original', 'amount_base', 'exchange_rate_snapshot']);
        });
    }
};
