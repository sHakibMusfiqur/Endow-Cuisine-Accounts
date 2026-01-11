# Restaurant Daily Accounting Web Application

A professional, production-ready daily accounting system built with Laravel 10, Blade templating, and MySQL. Designed specifically for restaurant financial management with role-based access control, real-time balance tracking, and comprehensive reporting features.

## 🌟 Features

### Core Functionality
- ✅ **Daily Transaction Management** - Record income and expenses with automatic balance calculation
- ✅ **Role-Based Access Control** - Admin, Accountant, and Manager roles with specific permissions
- ✅ **Real-Time Balance Tracking** - Automatic balance calculation after each transaction
- ✅ **Category Management** - Organize transactions by income/expense categories
- ✅ **Payment Method Tracking** - Track Cash, Card, and other payment methods
- ✅ **Smart Notifications** - Alerts for high expenses and low balance
- ✅ **Advanced Reporting** - Export data to CSV, PDF, and summary reports
- ✅ **Dashboard Analytics** - Visual charts and statistics for quick insights

### Business Logic
- Income and expense cannot both be > 0 in a single transaction
- Automatic balance recalculation when transactions are added/edited/deleted
- Sequential balance maintenance across all transactions
- Notification triggers for financial thresholds
- Transaction validation and data integrity

## 🎯 User Roles & Permissions

| Feature | Admin | Accountant | Manager |
|---------|-------|------------|---------|
| View Dashboard | ✅ | ✅ | ✅ |
| View Transactions | ✅ | ✅ | ✅ |
| Create/Edit Transactions | ✅ | ✅ | ❌ |
| Delete Transactions | ✅ | ❌ | ❌ |
| Manage Categories | ✅ | ❌ | ❌ |
| Manage Payment Methods | ✅ | ❌ | ❌ |
| View Reports | ✅ | ✅ | ✅ |
| Export Data | ✅ | ✅ | ✅ |

## 📋 System Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7 or MariaDB >= 10.3
- Node.js & NPM (optional, for asset compilation)

## 🚀 Installation & Setup

### Step 1: Configure Environment

Update the `.env` file with your database credentials:

```env
APP_NAME="Restaurant Accounting"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_accounting
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 2: Generate Application Key

```bash
php artisan key:generate
```

### Step 3: Create Database

Create a new MySQL database:

```sql
CREATE DATABASE restaurant_accounting;
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

### Step 5: Seed Database

```bash
php artisan db:seed
```

This will create:
- **Default users** (see credentials below)
- **15 categories** (5 income, 10 expense categories)
- **5 payment methods** (Cash, Card, etc.)

### Step 6: Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

## 🔐 Default Login Credentials

### Admin Account
- **Email:** admin@restaurant.com
- **Password:** password
- **Permissions:** Full access to all features

### Accountant Account
- **Email:** accountant@restaurant.com
- **Password:** password
- **Permissions:** Create/Edit transactions, View reports

### Manager Account
- **Email:** manager@restaurant.com
- **Password:** password
- **Permissions:** View-only access to dashboard and reports

## 🎨 Key Features Explained

### 1. Automatic Balance Calculation
The system automatically:
- Retrieves the last balance before the transaction date
- Calculates new balance: `new_balance = last_balance + income - expense`
- Updates all subsequent transaction balances

### 2. Smart Notifications
Automatically triggers when:
- **High Expense:** Single expense > ₹5,000
- **Low Balance:** Account balance < ₹10,000

### 3. Export Options
- **CSV Export:** For Excel and spreadsheet applications
- **PDF Reports:** Print-ready transaction statements
- **Summary Reports:** Category-wise and payment method-wise analysis

## 🔧 Quick Start Summary

```bash
# 1. Generate application key
php artisan key:generate

# 2. Create database named: restaurant_accounting

# 3. Run migrations and seeders
php artisan migrate --seed

# 4. Start server
php artisan serve

# 5. Login at http://localhost:8000
# Use: admin@restaurant.com / password
```

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## 🎨 Logo Setup (NEW!)

This application now includes logo placeholder support across all pages. You can easily add your restaurant's logo to personalize the application.

### Quick Logo Setup

1. **Prepare your logo:**
   - Format: PNG with transparent background
   - Size: 200x200px (square)
   - File size: Under 100KB

2. **Add to project:**
   ```powershell
   # Copy to the images directory
   copy "path\to\your\logo.png" "public\images\logo.png"
   ```

3. **Clear cache:**
   ```powershell
   php artisan cache:clear
   php artisan view:clear
   ```

4. **Refresh browser** - Your logo will now appear!

### Helper Script

Use the automated setup script:
```powershell
.\setup-logo.ps1
```

### Logo Locations

Your logo will automatically appear in:
- ✅ Sidebar navigation (all pages)
- ✅ Login page
- ✅ Password reset pages
- ✅ Welcome page
- ✅ Email templates

### Documentation

- **Quick Reference:** [`LOGO_QUICK_REFERENCE.md`](LOGO_QUICK_REFERENCE.md)
- **Full Guide:** [`LOGO_IMPLEMENTATION.md`](LOGO_IMPLEMENTATION.md)
- **Summary:** [`LOGO_IMPLEMENTATION_SUMMARY.md`](LOGO_IMPLEMENTATION_SUMMARY.md)

**Note:** The application works perfectly without a logo - fallback icons are displayed automatically.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
