# Testing Guide: Role & Permission System

## 🧪 Manual Testing Checklist

### Prerequisites
1. Ensure seeder has been run: `php artisan db:seed --class=RolePermissionSeeder`
2. Users have been assigned correct roles: `php assign-user-roles.php`
3. Cache has been cleared: `php artisan permission:cache-reset`

---

## Test Plan by Role

### 🔴 Test 1: Admin User
**Login as:** admin@restaurant.com

#### Expected Behavior:
- ✅ Can see "+ Add New Transaction" button on transactions page
- ✅ Can click and access `/transactions/create` page
- ✅ Can create new transactions
- ✅ Can see "Edit" button on each transaction
- ✅ Can click and access `/transactions/{id}/edit` page
- ✅ Can edit transactions
- ✅ Can see "Delete" button on each transaction
- ✅ Can delete transactions
- ✅ Can access "Categories" menu in sidebar
- ✅ Can access "Payment Methods" menu in sidebar
- ✅ Can access currency settings

#### Test Steps:
1. Log in as admin@restaurant.com
2. Navigate to Transactions page
3. Verify "+ Add New Transaction" button is visible
4. Click button - should open create form
5. Fill and submit form - should create transaction
6. Go back to transactions list
7. Find a transaction and click "Edit" - should open edit form
8. Update transaction - should save changes
9. Find a transaction and click "Delete" - should show confirmation
10. Confirm deletion - transaction should be deleted
11. Check sidebar - Categories and Payment Methods should be visible
12. Click Categories - should access category management
13. Click Payment Methods - should access payment methods management

---

### 🟡 Test 2: Accountant User
**Login as:** accountant@restaurant.com

#### Expected Behavior:
- ✅ Can see "+ Add New Transaction" button on transactions page
- ✅ Can create new transactions
- ✅ Can see "Edit" button on each transaction
- ✅ Can edit transactions
- ❌ Cannot see "Delete" button on transactions
- ❌ Cannot access `/transactions/{id}/destroy` (403 if attempted via URL)
- ❌ Cannot see "Categories" menu in sidebar
- ❌ Cannot see "Payment Methods" menu in sidebar
- ❌ Cannot access category/payment method management (403 if attempted via URL)

#### Test Steps:
1. Log in as accountant@restaurant.com
2. Navigate to Transactions page
3. Verify "+ Add New Transaction" button is visible
4. Click button and create a transaction - should succeed
5. Verify "Edit" button is visible on transactions
6. Click "Edit" and update a transaction - should succeed
7. Verify "Delete" button is NOT visible
8. Check sidebar - Categories and Payment Methods should NOT be visible
9. Try accessing `/categories` directly in browser - should get 403 error
10. Try accessing `/payment-methods` directly - should get 403 error

---

### 🟢 Test 3: Manager User
**Login as:** manager@restaurant.com

#### Expected Behavior:
- ❌ Cannot see "+ Add New Transaction" button on transactions page
- ❌ Cannot access `/transactions/create` (403 if attempted via URL)
- ❌ Cannot see "Edit" button on transactions
- ❌ Cannot see "Delete" button on transactions
- ✅ Can see transactions (view-only mode)
- ✅ Can see "View Only" badge/button on transactions
- ❌ Cannot access `/transactions/{id}/edit` (403 if attempted via URL)
- ❌ Cannot see "Categories" menu in sidebar
- ❌ Cannot see "Payment Methods" menu in sidebar
- ✅ Can access Dashboard
- ✅ Can access Reports

#### Test Steps:
1. Log in as manager@restaurant.com
2. Navigate to Transactions page
3. Verify "+ Add New Transaction" button is NOT visible
4. Verify "Edit" and "Delete" buttons are NOT visible
5. Verify "View Only" indicator is shown
6. Try accessing `/transactions/create` directly - should get 403 error
7. Try accessing `/transactions/1/edit` directly - should get 403 error
8. Check sidebar - Categories and Payment Methods should NOT be visible
9. Verify Dashboard is accessible
10. Verify Reports page is accessible

---

## 🔒 Security Tests

### Test 4: Direct URL Access (Security)
These tests verify that backend security is working, not just UI hiding.

#### Test as Manager:
1. Log in as manager@restaurant.com
2. Try to access these URLs directly in browser:
   - `/transactions/create` → Should return **403 Forbidden**
   - `/transactions/1/edit` → Should return **403 Forbidden**
   - `/categories` → Should return **403 Forbidden**
   - `/payment-methods` → Should return **403 Forbidden**
   - `/currencies` → Should return **403 Forbidden**

#### Test as Accountant:
1. Log in as accountant@restaurant.com
2. Try to access these URLs directly:
   - `/categories` → Should return **403 Forbidden**
   - `/payment-methods` → Should return **403 Forbidden**
   - `/currencies` → Should return **403 Forbidden**

---

## 🎯 UI Consistency Tests

### Test 5: Button Visibility (Critical)
This is the main issue that was fixed - the "+ Add New Transaction" button.

1. **Admin Login:**
   - Go to `/transactions`
   - Button should be visible at top right ✅
   - Go to empty transactions page (filter to show no results)
   - Button should still be visible in empty state ✅

2. **Accountant Login:**
   - Go to `/transactions`
   - Button should be visible at top right ✅
   - Go to empty transactions page
   - Button should be visible in empty state ✅

3. **Manager Login:**
   - Go to `/transactions`
   - Button should NOT be visible ❌
   - Go to empty transactions page
   - Button should NOT be visible ❌

---

## 📊 Expected Results Summary

| Feature | Admin | Accountant | Manager |
|---------|-------|------------|---------|
| View Transactions | ✅ | ✅ | ✅ |
| "+ Add New Transaction" Button | ✅ | ✅ | ❌ |
| Create Transactions | ✅ | ✅ | ❌ |
| Edit Transactions | ✅ | ✅ | ❌ |
| Delete Transactions | ✅ | ❌ | ❌ |
| Categories Menu | ✅ | ❌ | ❌ |
| Payment Methods Menu | ✅ | ❌ | ❌ |
| Currencies | ✅ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ |
| Dashboard | ✅ | ✅ | ✅ |

---

## 🐛 Common Issues & Solutions

### Issue: Button not showing for Admin/Accountant
**Solution:**
```bash
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
```
Then log out and log back in.

### Issue: Getting 403 errors as Admin
**Solution:**
1. Check user role:
```bash
php artisan tinker
```
```php
$user = App\Models\User::where('email', 'admin@restaurant.com')->first();
$user->roles->pluck('name'); // Should show ['admin']
$user->getAllPermissions()->pluck('name'); // Should show all permissions
```

2. If role is wrong, fix it:
```bash
php assign-user-roles.php
```

### Issue: Changes not taking effect
**Solution:**
1. Clear browser cache (Ctrl+Shift+R)
2. Log out and log back in
3. Clear Laravel cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan permission:cache-reset
```

---

## ✅ Success Criteria

All tests pass when:
- ✅ Admin can do everything
- ✅ Accountant can create/edit but not delete
- ✅ Manager can only view
- ✅ "+ Add New Transaction" button always shows for Admin and Accountant
- ✅ Button never shows for Manager
- ✅ Direct URL access is blocked by permissions
- ✅ No 403 errors for authorized users
- ✅ Sidebar menu items are permission-based

---

## 🚀 Performance Test

After all tests pass, verify performance:
1. Page load time should be normal (< 2 seconds)
2. No permission-related queries slowing down requests
3. Cache is working correctly

Check logs for any permission-related errors:
```bash
tail -f storage/logs/laravel.log
```

---

**Last Updated:** January 10, 2026
