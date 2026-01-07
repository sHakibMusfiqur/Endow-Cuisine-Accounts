# ⚡ QUICK REFERENCE CARD

## 🔐 Login Credentials
```
Admin:      admin@restaurant.com / password
Accountant: accountant@restaurant.com / password
Manager:    manager@restaurant.com / password
```

## 🚀 Quick Start Commands
```bash
# Start server
php artisan serve

# Clear cache
php artisan cache:clear
php artisan config:clear

# Reset database (WARNING: Deletes all data!)
php artisan migrate:fresh --seed

# View routes
php artisan route:list

# Check migrations status
php artisan migrate:status
```

## 📊 Key Features

### Dashboard
- Today's Income/Expense/Balance
- Weekly & Monthly charts
- Recent transactions
- Top expense categories

### Transactions
- **Filters:** Date, Category, Payment Method, Type, Search
- **Actions:** Create, Edit, Delete (role-dependent)
- **Auto-calculation:** Balance updates automatically

### Reports
- CSV Export (date range)
- PDF View (date range)
- Summary Reports (category & payment wise)
- Quick Reports (Today, Week, Month, Year)

## 🎯 Common Tasks

### Add Transaction
1. Transactions → Add Transaction
2. Select Type (Income/Expense)
3. Fill date, description, amount
4. Select category & payment method
5. Save → Balance auto-calculates!

### View Summary
Dashboard → See cards and charts

### Export Data
Reports → Select date range → Export CSV

### Manage Categories (Admin only)
Categories → Add/Edit/Delete

### Check Notifications
Click bell icon in sidebar

## 🔧 Notification Thresholds

```php
High Expense: > ₹5,000
Low Balance: < ₹10,000
```

To change: Edit `app/Services/TransactionService.php`

## 📱 URLs

```
Application: http://localhost:8000
Login: http://localhost:8000/login
Dashboard: http://localhost:8000/dashboard
Transactions: http://localhost:8000/transactions
Reports: http://localhost:8000/reports
```

## 🐛 Common Issues & Fixes

### "Access denied for user"
→ Check DB credentials in `.env`

### "Base table not found"
→ Run: `php artisan migrate`

### "No application key"
→ Run: `php artisan key:generate`

### White screen / Not loading
→ Run: `php artisan config:clear && php artisan cache:clear`

### Can't login
→ Ensure you ran: `php artisan db:seed`

## ⚠️ Important Business Rules

1. **Income OR Expense** - A transaction cannot have both
2. **Balance Auto-calculates** - Never manually edit
3. **Sequential Updates** - All balances recalculate when editing
4. **Category Matching** - Income category for income transaction only
5. **Soft Delete Protection** - Cannot delete categories/payment methods with transactions

## 🎨 Customization

### Change Currency
Replace `₹` with your symbol in views:
- `dashboard/index.blade.php`
- `transactions/index.blade.php`
- `reports/index.blade.php`

### Change App Name
Edit `.env`:
```env
APP_NAME="Your Restaurant Name"
```

### Add More Categories
Login as Admin → Categories → Add Category

## 📊 Database Tables

```
users              → Authentication & roles
categories         → Income/Expense types
payment_methods    → Payment tracking
daily_transactions → Core accounting (with balance)
notifications      → System alerts
```

## 🎯 Role Permissions Quick Guide

| Action | Admin | Accountant | Manager |
|--------|-------|------------|---------|
| View Dashboard | ✅ | ✅ | ✅ |
| Create Transaction | ✅ | ✅ | ❌ |
| Edit Transaction | ✅ | ✅ | ❌ |
| Delete Transaction | ✅ | ❌ | ❌ |
| Manage Categories | ✅ | ❌ | ❌ |
| Manage Payments | ✅ | ❌ | ❌ |
| Export Reports | ✅ | ✅ | ✅ |

## 💾 Backup Reminder

**Manual Backup:**
```bash
mysqldump -u root -p restaurant_accounting > backup_$(date +%Y%m%d).sql
```

**Restore:**
```bash
mysql -u root -p restaurant_accounting < backup_20260107.sql
```

## 📞 Need More Help?

1. Check `README.md` for detailed documentation
2. See `SETUP_GUIDE.md` for step-by-step instructions
3. Read `PROJECT_SUMMARY.md` for complete overview
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Keep this card handy for quick reference! 📌**
