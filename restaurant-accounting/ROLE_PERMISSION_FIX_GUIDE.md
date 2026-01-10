# Role & Permission System - Implementation Guide

## 🎯 Overview
This document describes the standardized role and permission system implemented using Laravel Spatie Permission package.

---

## ✅ Changes Made

### 1. **Standardized Roles** (Lowercase Only)
- `admin` - Full system access
- `accountant` - Transaction management + reports
- `manager` - View-only access

**Important:** All role names are lowercase to prevent inconsistencies.

---

### 2. **Standard Permissions Created**

#### Transaction Permissions:
- `create transactions` - Create new transactions
- `edit transactions` - Edit existing transactions
- `delete transactions` - Delete transactions
- `view transactions` - View transaction list

#### System Permissions:
- `manage users` - User management
- `manage categories` - Category management
- `manage payment methods` - Payment method management
- `manage currencies` - Currency management
- `view reports` - Access reports
- `view dashboard` - Access dashboard

---

### 3. **Permission Assignment Matrix**

| Permission | Admin | Accountant | Manager |
|-----------|-------|------------|---------|
| create transactions | ✅ | ✅ | ❌ |
| edit transactions | ✅ | ✅ | ❌ |
| delete transactions | ✅ | ❌ | ❌ |
| view transactions | ✅ | ✅ | ✅ |
| manage users | ✅ | ❌ | ❌ |
| manage categories | ✅ | ❌ | ❌ |
| manage payment methods | ✅ | ❌ | ❌ |
| manage currencies | ✅ | ❌ | ❌ |
| view reports | ✅ | ✅ | ✅ |
| view dashboard | ✅ | ✅ | ✅ |

---

### 4. **Route Protection Updated**

**Before (❌ Role-based):**
```php
Route::middleware('role:admin,accountant')->group(function () {
    Route::get('/transactions/create', [TransactionController::class, 'create']);
});
```

**After (✅ Permission-based):**
```php
Route::middleware('can:create transactions')->group(function () {
    Route::get('/transactions/create', [TransactionController::class, 'create']);
});
```

---

### 5. **Blade Template Changes**

**Before (❌ Role-based):**
```blade
@role('admin|accountant')
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">
        Add Transaction
    </a>
@endrole
```

**After (✅ Permission-based):**
```blade
@can('create transactions')
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">
        Add Transaction
    </a>
@endcan
```

**Benefits:**
- ✅ Works regardless of role names
- ✅ Future-proof (new roles work automatically)
- ✅ Follows Laravel/Spatie best practices
- ✅ Single source of truth (permissions)

---

### 6. **Files Modified**

1. **database/seeders/RolePermissionSeeder.php**
   - Complete rewrite for standardization
   - Cleanup of duplicate/incorrect roles
   - Permission-to-role assignment
   - User migration logic

2. **routes/web.php**
   - All transaction routes now use `can:` middleware
   - Categories, payment methods, currencies use permission checks

3. **resources/views/transactions/index.blade.php**
   - "+ Add New Transaction" button visibility based on `@can('create transactions')`
   - Edit button visibility based on `@can('edit transactions')`
   - Delete button visibility based on `@can('delete transactions')`
   - View-only button for users without edit permission

4. **resources/views/layouts/app.blade.php**
   - Sidebar navigation items use `@can('manage categories')` instead of `@role('admin')`

---

## 🚀 Running the Migration

### Step 1: Run the Seeder
```bash
php artisan db:seed --class=RolePermissionSeeder
```

**What it does:**
1. Removes duplicate/incorrect roles
2. Creates standardized permissions
3. Creates standardized roles (admin, accountant, manager)
4. Assigns permissions to roles
5. Migrates existing users to correct roles

### Step 2: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan permission:cache-reset
```

### Step 3: Verify Users
Check your users table to ensure everyone has the correct role:
```bash
php artisan tinker
```
```php
\App\Models\User::with('roles', 'permissions')->get()->each(function($user) {
    echo "{$user->name} ({$user->email}): " . $user->roles->pluck('name')->implode(', ') . "\n";
});
```

---

## 🧪 Testing Checklist

### Admin User
- [ ] Can see "+ Add New Transaction" button
- [ ] Can create new transactions
- [ ] Can edit existing transactions
- [ ] Can delete transactions
- [ ] Can access Categories menu
- [ ] Can access Payment Methods menu
- [ ] Can access Currencies settings

### Accountant User
- [ ] Can see "+ Add New Transaction" button
- [ ] Can create new transactions
- [ ] Can edit existing transactions
- [ ] Cannot delete transactions
- [ ] Cannot access Categories menu
- [ ] Cannot access Payment Methods menu
- [ ] Cannot access Currencies settings

### Manager User
- [ ] Cannot see "+ Add New Transaction" button
- [ ] Cannot access `/transactions/create` directly (403 error)
- [ ] Cannot edit transactions (no edit button shown)
- [ ] Cannot delete transactions
- [ ] Cannot access Categories menu
- [ ] Cannot access Payment Methods menu
- [ ] Can view transactions (view-only mode)
- [ ] Can access Reports
- [ ] Can access Dashboard

---

## 🔐 Security Notes

1. **Backend Protection**: Even if someone manipulates the UI, routes are protected with middleware
2. **Direct URL Access**: Trying to access `/transactions/create` as Manager returns 403 Forbidden
3. **Permission-First**: UI checks permissions, not roles - prevents future breakage
4. **Single Source of Truth**: Permissions define what users can do

---

## 🎓 Best Practices Followed

### ✅ DO:
- Use `@can('permission name')` in Blade templates
- Use `can:permission name` in route middleware
- Use descriptive permission names with spaces
- Keep role names lowercase
- Assign permissions to roles, not users directly

### ❌ DON'T:
- Use `@role()` for UI logic
- Hard-code role names in Blade templates
- Mix role and permission checks
- Create uppercase role names
- Assign permissions directly to users (use roles instead)

---

## 📝 Adding New Roles (Future)

If you need to add a new role (e.g., "supervisor"):

1. **Update RolePermissionSeeder.php:**
```php
$supervisor = Role::create(['name' => 'supervisor']);
$supervisor->givePermissionTo([
    'create transactions',
    'edit transactions',
    'view transactions',
    'view reports',
    'view dashboard',
]);
```

2. **Run the seeder:**
```bash
php artisan db:seed --class=RolePermissionSeeder
```

3. **No UI changes needed!** The permission-based checks will automatically work.

---

## 🐛 Troubleshooting

### Issue: Button not showing for admin/accountant
**Solution:** 
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Issue: "403 Forbidden" when accessing routes
**Solution:** Check if user has the correct role:
```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('email', 'user@example.com')->first();
$user->roles; // Should show the role
$user->permissions; // Should show inherited permissions
```

### Issue: Seeder fails with "Role already exists"
**Solution:** The seeder uses `firstOrCreate()`, so it should work. If not:
```bash
php artisan permission:cache-reset
```

---

## 📊 System Architecture

```
USER
  └── assigned to → ROLE (admin, accountant, manager)
                     └── has → PERMISSIONS
                                └── control → UI VISIBILITY & ROUTE ACCESS
```

**Flow:**
1. User logs in
2. System checks user's role
3. Role provides permissions
4. Blade templates check `@can('permission')`
5. Routes check `can:permission` middleware
6. Access granted or denied based on permissions

---

## 🎉 Benefits of This Implementation

1. **Reliability**: Permission-based, not role-name dependent
2. **Scalability**: Easy to add new roles without changing code
3. **Maintainability**: Single source of truth (permissions)
4. **Security**: Backend enforcement, not just UI hiding
5. **Best Practices**: Follows Laravel + Spatie standards
6. **Future-Proof**: No hardcoded role names in views

---

## ✨ Result

✅ All roles are standardized and consistent
✅ Permission logic is clean and scalable
✅ "+ Add New Transaction" button always appears correctly for authorized users
✅ No future UI breaks due to role mismatches
✅ System follows enterprise-grade Laravel + Spatie standards

---

**Last Updated:** January 10, 2026
**Package:** Spatie Laravel Permission
**Laravel Version:** 10.x
