# 🚀 QUICK START - Role & Permission System

## After Fresh Install or Deploy

### 1️⃣ Run Database Seeder
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 2️⃣ Assign User Roles
```bash
php assign-user-roles.php
```

### 3️⃣ Clear Caches
```bash
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
```

### 4️⃣ Verify Setup
```bash
php verify-permissions.php
```

**Expected Output:** All checks should show ✅

---

## 🔍 Quick Verification

### Check User Roles:
```bash
php artisan tinker
```
```php
App\Models\User::with('roles')->get()->each(function($user) {
    echo $user->email . ': ' . $user->roles->pluck('name')->implode(', ') . "\n";
});
```

### Check Permissions for Role:
```php
$role = Spatie\Permission\Models\Role::findByName('accountant');
$role->permissions->pluck('name');
```

---

## 👥 User Credentials

| Email | Role | Password (default) |
|-------|------|-------------------|
| admin@restaurant.com | admin | (your password) |
| accountant@restaurant.com | accountant | (your password) |
| manager@restaurant.com | manager | (your password) |

---

## 🎯 Quick Permission Reference

### Transaction Permissions:
- `create transactions` - Admin, Accountant
- `edit transactions` - Admin, Accountant
- `delete transactions` - Admin only
- `view transactions` - All roles

### Management Permissions:
- `manage categories` - Admin only
- `manage payment methods` - Admin only
- `manage currencies` - Admin only
- `manage users` - Admin only

### General Permissions:
- `view reports` - All roles
- `view dashboard` - All roles

---

## 📋 Common Tasks

### Add New User with Role:
```php
$user = App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password123'),
]);

$user->assignRole('accountant');
```

### Change User Role:
```php
$user = App\Models\User::where('email', 'john@example.com')->first();
$user->syncRoles(['admin']); // Removes old roles, assigns new
```

### Check if User Can:
```php
$user->can('create transactions'); // Returns true/false
$user->hasPermissionTo('delete transactions'); // Same
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| Button not showing | Clear cache: `php artisan permission:cache-reset` |
| 403 errors | Check user role: `User::find(1)->roles` |
| Permissions not working | Re-run seeder + clear cache |
| Changes not visible | Log out and log back in |

---

## 📖 Full Documentation

- `IMPLEMENTATION_SUMMARY.md` - Complete overview
- `ROLE_PERMISSION_FIX_GUIDE.md` - Detailed implementation
- `TESTING_GUIDE.md` - Testing procedures

---

## ✅ System Status

- ✅ Roles: admin, accountant, manager
- ✅ Permissions: 10 total
- ✅ Routes: Protected with `can:` middleware
- ✅ Views: Using `@can()` directives
- ✅ Security: Backend + Frontend protection

---

**System Ready!** 🎉

All role issues fixed. The "+ Add New Transaction" button now works correctly and will always appear for authorized users (Admin and Accountant) only.
