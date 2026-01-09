<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage_users',
            'manage_transactions',
            'create_transactions',
            'edit_transactions',
            'delete_transactions',
            'view_reports',
            'manage_categories',
            'manage_payment_methods',
            'manage_currencies',
            'view_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - full access
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Accountant role - accounting and reports
        $accountantRole = Role::create(['name' => 'accountant']);
        $accountantRole->givePermissionTo([
            'manage_transactions',
            'create_transactions',
            'edit_transactions',
            'view_reports',
            'view_dashboard',
        ]);

        // Manager role - reports and dashboard only
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view_reports',
            'view_dashboard',
        ]);

        // Migrate existing users to Spatie roles
        $this->migrateExistingUsers();

        $this->command->info('Roles and permissions created successfully!');
    }

    /**
     * Migrate existing users from enum role to Spatie roles
     */
    private function migrateExistingUsers(): void
    {
        // This will run only if users exist in the database
        $users = User::all();

        foreach ($users as $user) {
            // Check if user has old role field (before migration)
            if (isset($user->role)) {
                $roleName = $user->role;
                
                // Assign Spatie role
                if (!$user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                    $this->command->info("Assigned role '{$roleName}' to user: {$user->email}");
                }
            }
        }
    }
}
