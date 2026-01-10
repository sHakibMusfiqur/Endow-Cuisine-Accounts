#!/usr/bin/env php
<?php
/**
 * Role & Permission Verification Script
 * Run this after seeding to verify everything is set up correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   Role & Permission System Verification                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Check Roles
echo "📋 Checking Roles...\n";
echo str_repeat('-', 60) . "\n";

$expectedRoles = ['admin', 'accountant', 'manager'];
$roles = Role::all();

foreach ($expectedRoles as $roleName) {
    $role = $roles->where('name', $roleName)->first();
    if ($role) {
        echo "✅ Role '{$roleName}' exists\n";
    } else {
        echo "❌ Role '{$roleName}' NOT FOUND\n";
    }
}

$extraRoles = $roles->whereNotIn('name', $expectedRoles);
if ($extraRoles->count() > 0) {
    echo "\n⚠️  WARNING: Extra roles found:\n";
    foreach ($extraRoles as $role) {
        echo "   - {$role->name}\n";
    }
}

echo "\n";

// Check Permissions
echo "🔐 Checking Permissions...\n";
echo str_repeat('-', 60) . "\n";

$expectedPermissions = [
    'create transactions',
    'edit transactions',
    'delete transactions',
    'view transactions',
    'manage users',
    'manage categories',
    'manage payment methods',
    'manage currencies',
    'view reports',
    'view dashboard',
];

$permissions = Permission::all();

foreach ($expectedPermissions as $permissionName) {
    $permission = $permissions->where('name', $permissionName)->first();
    if ($permission) {
        echo "✅ Permission '{$permissionName}' exists\n";
    } else {
        echo "❌ Permission '{$permissionName}' NOT FOUND\n";
    }
}

echo "\n";

// Check Permission Assignments
echo "🔗 Checking Permission Assignments...\n";
echo str_repeat('-', 60) . "\n";

$permissionMatrix = [
    'admin' => [
        'create transactions',
        'edit transactions',
        'delete transactions',
        'view transactions',
        'manage users',
        'manage categories',
        'manage payment methods',
        'manage currencies',
        'view reports',
        'view dashboard',
    ],
    'accountant' => [
        'create transactions',
        'edit transactions',
        'view transactions',
        'view reports',
        'view dashboard',
    ],
    'manager' => [
        'view transactions',
        'view reports',
        'view dashboard',
    ],
];

foreach ($permissionMatrix as $roleName => $expectedPermissions) {
    $role = Role::findByName($roleName);
    echo "\n📌 Role: {$roleName}\n";
    
    foreach ($expectedPermissions as $permissionName) {
        if ($role->hasPermissionTo($permissionName)) {
            echo "   ✅ {$permissionName}\n";
        } else {
            echo "   ❌ {$permissionName} - NOT ASSIGNED\n";
        }
    }
}

echo "\n";

// Check Users
echo "👥 Checking Users...\n";
echo str_repeat('-', 60) . "\n";

$users = User::with('roles')->get();

if ($users->count() === 0) {
    echo "⚠️  No users found in database\n";
} else {
    foreach ($users as $user) {
        $roleNames = $user->roles->pluck('name')->toArray();
        $roleCount = count($roleNames);
        
        if ($roleCount === 0) {
            echo "❌ {$user->email} - NO ROLE ASSIGNED\n";
        } elseif ($roleCount === 1) {
            $roleName = $roleNames[0];
            $icon = in_array($roleName, $expectedRoles) ? '✅' : '⚠️';
            echo "{$icon} {$user->email} - Role: {$roleName}\n";
        } else {
            echo "⚠️  {$user->email} - MULTIPLE ROLES: " . implode(', ', $roleNames) . "\n";
        }
    }
}

echo "\n";

// Permission Testing
echo "🧪 Testing Permission Checks...\n";
echo str_repeat('-', 60) . "\n";

$testCases = [
    'admin' => [
        'can create transactions' => 'create transactions',
        'can edit transactions' => 'edit transactions',
        'can delete transactions' => 'delete transactions',
        'can manage categories' => 'manage categories',
    ],
    'accountant' => [
        'can create transactions' => 'create transactions',
        'can edit transactions' => 'edit transactions',
        'cannot delete transactions' => 'delete transactions',
        'cannot manage categories' => 'manage categories',
    ],
    'manager' => [
        'can view transactions' => 'view transactions',
        'cannot create transactions' => 'create transactions',
        'cannot edit transactions' => 'edit transactions',
        'cannot delete transactions' => 'delete transactions',
    ],
];

foreach ($testCases as $roleName => $tests) {
    $role = Role::findByName($roleName);
    echo "\n🔍 Testing {$roleName} role:\n";
    
    foreach ($tests as $description => $permissionName) {
        $hasPermission = $role->hasPermissionTo($permissionName);
        $shouldHave = strpos($description, 'cannot') === false;
        
        if ($hasPermission === $shouldHave) {
            echo "   ✅ {$description}\n";
        } else {
            echo "   ❌ {$description} - FAILED\n";
        }
    }
}

echo "\n";

// Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   Verification Complete                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "✅ If all checks passed, your role & permission system is ready!\n";
echo "❌ If any checks failed, run: php artisan db:seed --class=RolePermissionSeeder\n";
echo "\n";
