<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🔧 Starting Role & Permission Setup...');

        // Step 1: Clean up any existing duplicate or incorrect roles
        $this->cleanupRoles();

        // Step 2: Create standardized permissions
        $this->createPermissions();

        // Step 3: Create standardized roles
        $this->createRoles();

        // Step 4: Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Step 5: Migrate existing users
        $this->migrateExistingUsers();

        $this->command->info('✅ Roles and permissions setup completed successfully!');
    }

    /**
     * Remove duplicate or incorrectly named roles
     */
    private function cleanupRoles(): void
    {
        $this->command->info('🧹 Cleaning up existing roles...');

        // Keep only lowercase standard roles
        $standardRoles = ['admin', 'accountant', 'manager'];
        
        // Get all existing roles
        $existingRoles = Role::all();
        
        foreach ($existingRoles as $role) {
            if (!in_array($role->name, $standardRoles)) {
                $this->command->warn("Removing non-standard role: {$role->name}");
                $role->delete();
            }
        }
    }

    /**
     * Create standardized permissions
     */
    private function createPermissions(): void
    {
        $this->command->info('📝 Creating permissions...');

        $permissions = [
            // Transaction permissions (as specified in requirements)
            'create transactions',
            'edit transactions',
            'delete transactions',
            'view transactions',
            
            // Additional system permissions
            'manage users',
            'manage categories',
            'manage payment methods',
            'manage currencies',
            'view reports',
            'view dashboard',
            
            // Inventory permissions
            'view inventory',
            'manage inventory',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $this->command->info("✓ Permission created: {$permission}");
        }
    }

    /**
     * Create standardized roles (lowercase only)
     */
    private function createRoles(): void
    {
        $this->command->info('👥 Creating roles...');

        $roles = ['admin', 'accountant', 'manager'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
            $this->command->info("✓ Role created: {$roleName}");
        }
    }

    /**
     * Assign permissions to roles according to requirements
     */
    private function assignPermissionsToRoles(): void
    {
        $this->command->info('🔐 Assigning permissions to roles...');

        // ADMIN - Full access to everything
        $admin = Role::findByName('admin');
        $admin->syncPermissions([
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
            'view inventory',
            'manage inventory',
        ]);
        $this->command->info('✓ Admin permissions assigned');

        // ACCOUNTANT - Can create, edit, delete transactions + reports + manage inventory (add, edit, delete items)
        $accountant = Role::findByName('accountant');
        $accountant->syncPermissions([
            'create transactions',
            'edit transactions',
            'view transactions',
            'view reports',
            'view dashboard',
            'view inventory',
            'manage inventory',  // Added to allow accountants to add, edit, and delete inventory items
        ]);
        $this->command->info('✓ Accountant permissions assigned');

        // MANAGER - View only (transactions and reports) + manage inventory
        $manager = Role::findByName('manager');
        $manager->syncPermissions([
            'view transactions',
            'view reports',
            'view dashboard',
            'view inventory',
            'manage inventory',
        ]);
        $this->command->info('✓ Manager permissions assigned');
    }

    /**
     * Migrate existing users to standardized roles
     */
    private function migrateExistingUsers(): void
    {
        $this->command->info('👤 Migrating existing users...');

        $users = User::all();

        foreach ($users as $user) {
            // Get the first role from the user's current roles
            $currentRoles = $user->roles->pluck('name')->toArray();
            
            if (empty($currentRoles)) {
                // If user has no role, check if they have an old role field
                if (isset($user->role)) {
                    $roleName = strtolower($user->role);
                } else {
                    // Default to manager for safety
                    $roleName = 'manager';
                    $this->command->warn("User {$user->email} has no role, assigning 'manager' by default");
                }
            } else {
                // Use the first role and ensure it's lowercase
                $roleName = strtolower($currentRoles[0]);
            }

            // Ensure the role exists
            if (!in_array($roleName, ['admin', 'accountant', 'manager'])) {
                $roleName = 'manager'; // Default fallback
                $this->command->warn("Invalid role for {$user->email}, assigning 'manager' by default");
            }

            // Sync user to have exactly one role
            $user->syncRoles([$roleName]);
            $this->command->info("✓ User {$user->email} assigned role: {$roleName}");
        }
    }
}
