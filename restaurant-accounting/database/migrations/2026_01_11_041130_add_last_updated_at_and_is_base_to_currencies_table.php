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
        Schema::table('currencies', function (Blueprint $table) {
            // Add last_updated_at for tracking when rates were last updated
            $table->timestamp('last_updated_at')->nullable()->after('is_active');
            
            // Add is_base to clearly identify base currency (KRW)
            // This will replace is_default conceptually but we keep both for backward compatibility
            $table->boolean('is_base')->default(false)->after('is_default');
            
            // Add index for is_base
            $table->index('is_base');
        });
        
        // Set KRW as the base currency
        DB::table('currencies')->where('code', 'KRW')->update([
            'is_base' => true,
            'is_default' => true,
            'exchange_rate' => 1.000000,
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropIndex(['is_base']);
            $table->dropColumn(['last_updated_at', 'is_base']);
        });
    }
};
