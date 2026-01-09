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
        Schema::table('users', function (Blueprint $table) {
            // Remove enum role field (will use Spatie instead)
            $table->dropColumn('role');
            
            // Add profile fields
            $table->string('profile_photo')->nullable()->after('password');
            $table->string('phone')->nullable()->after('profile_photo');
            $table->text('address')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('address');
            $table->timestamp('last_login_at')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add role field
            $table->enum('role', ['admin', 'accountant', 'manager'])->default('accountant');
            
            // Drop profile fields
            $table->dropColumn([
                'profile_photo',
                'phone',
                'address',
                'bio',
                'last_login_at'
            ]);
        });
    }
};
