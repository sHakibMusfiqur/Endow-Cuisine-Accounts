<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds batch_id to group multiple transaction entries that were
     * submitted together in a single multi-item form submission.
     */
    public function up(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            $table->string('batch_id', 50)->nullable()->after('internal_reference_id');
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_transactions', function (Blueprint $table) {
            $table->dropIndex(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
