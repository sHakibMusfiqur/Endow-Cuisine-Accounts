# Spatie Laravel Permission - Quick Reference Guide

## 🚀 Common Tasks

### **1. Check User Role**

#### In Blade Templates:
```blade
@role('admin')
    <p>Only admins can see this</p>
@endrole

@role('admin|accountant')
    <p>Admin OR Accountant can see this</p>
@endrole

@hasanyrole('admin|accountant')
    <p>Alternative syntax for multiple roles</p>
@endhasanyrole

@hasrole('admin')
    <p>Alternative syntax for single role</p>
@endhasrole
```

#### In Controllers/PHP:
```php
// Check if user has a specific role
if (auth()->user()->hasRole('admin')) {
    // Admin logic
}

// Check if user has any of the roles
if (auth()->user()->hasAnyRole(['admin', 'accountant'])) {
    // Logic for admin or accountant
}

// Check if user has all roles
if (auth()->user()->hasAllRoles(['admin', 'manager'])) {
    // User has both roles
}
```

### **2. Check User Permission**

#### In Blade Templates:
```blade
@can('manage_transactions')
    <a href="#">Manage Transactions</a>
@endcan

@cannot('delete_transactions')
    <p>You don't have permission to delete</p>
@endcannot
```

#### In Controllers/PHP:
```php
// Check permission
if (auth()->user()->can('manage_transactions')) {
    // Allow action
}

// Check multiple permissions (any)
if (auth()->user()->hasAnyPermission(['create_transactions', 'edit_transactions'])) {
    // Allow action
}

// Check multiple permissions (all)
if (auth()->user()->hasAllPermissions(['create_transactions', 'manage_categories'])) {
    // Allow action
}

// Authorize (throws exception if fails)
auth()->user()->authorizePermission('delete_transactions');
```

### **3. Assign Roles to Users**

```php
use App\Models\User;

$user = User::find(1);

// Assign a single role
$user->assignRole('admin');

// Assign multiple roles
$user->assignRole(['admin', 'manager']);

// Sync roles (removes old roles, adds new ones)
$user->syncRoles(['accountant']);

// Remove role
$user->removeRole('admin');

// Remove all roles
$user->syncRoles([]);
```

### **4. Assign Permissions to Users**

```php
$user = User::find(1);

// Give permission directly to user
$user->givePermissionTo('manage_transactions');

// Give multiple permissions
$user->givePermissionTo(['manage_transactions', 'view_reports']);

// Revoke permission
$user->revokePermissionTo('delete_transactions');

// Sync permissions
$user->syncPermissions(['view_reports', 'view_dashboard']);
```

### **5. Assign Permissions to Roles**

```php
use Spatie\Permission\Models\Role;

$role = Role::findByName('accountant');

// Give permission to role
$role->givePermissionTo('manage_transactions');

// Give multiple permissions
$role->givePermissionTo(['create_transactions', 'edit_transactions']);

// Revoke permission
$role->revokePermissionTo('delete_transactions');

// Sync permissions (removes old, adds new)
$role->syncPermissions(['view_reports', 'view_dashboard']);
```

### **6. Route Protection with Middleware**

#### In routes/web.php:
```php
// Single role
Route::middleware('role:admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Multiple roles (OR)
Route::middleware('role:admin,accountant')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
});

// Permission-based
Route::middleware('can:manage_transactions')->group(function () {
    Route::get('/transactions/create', [TransactionController::class, 'create']);
});
```

### **7. Get User Roles and Permissions**

```php
$user = auth()->user();

// Get all role names
$roles = $user->getRoleNames(); // Collection of role names
// Returns: collect(['admin', 'manager'])

// Get first role
$role = $user->roles->first()?->name;

// Get all permissions
$permissions = $user->getAllPermissions(); // Collection of permission objects

// Get permission names
$permissionNames = $user->getPermissionNames(); // Collection of permission names
```

### **8. Create New Roles and Permissions**

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create a new role
$role = Role::create(['name' => 'supervisor']);

// Create a new permission
$permission = Permission::create(['name' => 'export_reports']);

// Assign permission to role
$role->givePermissionTo($permission);

// Assign role to user
auth()->user()->assignRole($role);
```

### **9. Check in Policies**

```php
// app/Policies/TransactionPolicy.php
public function delete(User $user, Transaction $transaction)
{
    return $user->hasRole('admin');
}

public function update(User $user, Transaction $transaction)
{
    return $user->hasAnyRole(['admin', 'accountant']);
}

public function view(User $user, Transaction $transaction)
{
    return $user->can('view_reports');
}
```

### **10. Useful Artisan Commands**

```bash
# Clear permission cache (IMPORTANT after changes!)
php artisan permission:cache-reset

# Show all roles and permissions
php artisan permission:show

# Create a permission
php artisan permission:create-permission "manage_inventory"

# Create a role
php artisan permission:create-role "supervisor"

# Assign permission to role
php artisan permission:create-permission "manage_inventory" --role="supervisor"
```

---

## 🎯 Current Project Setup

### **Roles:**
- `admin` - Full access
- `accountant` - Transactions and reports
- `manager` - Reports and dashboard only

### **Permissions:**
- `manage_users`
- `manage_transactions`
- `create_transactions`
- `edit_transactions`
- `delete_transactions`
- `view_reports`
- `manage_categories`
- `manage_payment_methods`
- `manage_currencies`
- `view_dashboard`

### **Permission Assignment:**
```
Admin:
  ✓ All permissions

Accountant:
  ✓ manage_transactions
  ✓ create_transactions
  ✓ edit_transactions
  ✓ view_reports
  ✓ view_dashboard

Manager:
  ✓ view_reports
  ✓ view_dashboard
```

---

## ⚠️ Important Notes

1. **Always Clear Cache After Changes:**
   ```bash
   php artisan permission:cache-reset
   # Or
   php artisan optimize:clear
   ```

2. **Guard Names:**
   - Default guard is `web`
   - If using API, specify guard: `Role::create(['name' => 'admin', 'guard_name' => 'api'])`

3. **Database-Driven:**
   - All roles and permissions are stored in MySQL
   - No hard-coded checks
   - Easy to modify without code changes

4. **Performance:**
   - Spatie caches permissions for performance
   - Clear cache after making changes
   - Use `hasRole()` instead of checking role name directly

5. **Best Practices:**
   - Use `@role()` for role-based UI
   - Use `@can()` for permission-based UI
   - Use middleware for route protection
   - Use policies for model authorization

---

## 📚 Resources

- **Official Documentation**: https://spatie.be/docs/laravel-permission
- **GitHub**: https://github.com/spatie/laravel-permission
- **Laravel Authorization**: https://laravel.com/docs/authorization

---

**Quick Tip**: Use `php artisan tinker` to test roles and permissions interactively!

```php
php artisan tinker
>>> $user = User::first()
>>> $user->hasRole('admin')
>>> $user->getRoleNames()
>>> $user->getAllPermissions()
```
