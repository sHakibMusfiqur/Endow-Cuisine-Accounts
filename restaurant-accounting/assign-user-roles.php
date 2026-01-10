<?php
/**
 * Assign correct roles to users based on email
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n🔧 Assigning correct roles to users...\n\n";

// Define user-role mappings based on email
$userRoles = [
    'admin@restaurant.com' => 'admin',
    'accountant@restaurant.com' => 'accountant',
    'manager@restaurant.com' => 'manager',
];

foreach ($userRoles as $email => $roleName) {
    $user = User::where('email', $email)->first();
    
    if ($user) {
        $user->syncRoles([$roleName]);
        echo "✅ {$email} assigned role: {$roleName}\n";
    } else {
        echo "⚠️  User not found: {$email}\n";
    }
}

// List all users with their roles
echo "\n📋 Current User Roles:\n";
echo str_repeat('-', 60) . "\n";

$users = User::with('roles')->get();
foreach ($users as $user) {
    $role = $user->roles->pluck('name')->first() ?? 'NO ROLE';
    echo "{$user->email} - Role: {$role}\n";
}

echo "\n✅ Done!\n\n";
