# 🚀 Real Estate CRM - Complete Setup Guide

**Last Updated**: January 29, 2026
**Status**: Ready for Installation

---

## 📋 Pre-Installation Checklist

Before you begin, ensure you have:

- [ ] PHP 8.2 or higher installed
- [ ] MySQL 8.0 or higher running
- [ ] Composer installed globally
- [ ] Node.js 18+ and NPM 9+ installed
- [ ] Git (optional, for version control)
- [ ] Code editor (VS Code recommended)

---

## 🔧 Step-by-Step Installation

### Step 1: Environment Setup

```powershell
# Navigate to your project directory
cd c:\Sites\realestatecrm

# Copy environment file
copy .env.example .env
```

### Step 2: Configure Database

Edit `.env` file and set your database credentials:

```env
APP_NAME="Real Estate CRM"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=realestatecrm
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Step 3: Create Database

```sql
-- Run this in MySQL:
CREATE DATABASE realestatecrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or using command line:
```powershell
mysql -u root -p -e "CREATE DATABASE realestatecrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 4: Install Dependencies

```powershell
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### Step 5: Generate Application Key

```powershell
php artisan key:generate
```

### Step 6: Run Migrations

```powershell
# This will create all 32 database tables
php artisan migrate
```

**Expected Output**:
```
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (150.00ms)
Migrating: 2026_01_28_000001_create_roles_table
Migrated:  2026_01_28_000001_create_roles_table (120.00ms)
... (30 more migrations)
```

### Step 7: Seed Database

```powershell
# This will create roles, permissions, and default users
php artisan db:seed
```

**Expected Output**:
```
Seeding: Database\Seeders\RoleSeeder
Seeded:  Database\Seeders\RoleSeeder (50.00ms)
Seeding: Database\Seeders\PermissionSeeder
Seeded:  Database\Seeders\PermissionSeeder (200.00ms)
... (3 more seeders)
```

### Step 8: Build Frontend Assets

```powershell
# For development
npm run dev

# OR for production
npm run build
```

### Step 9: Start Development Server

```powershell
# Start Laravel server
php artisan serve
```

**Server will start at**: `http://localhost:8000`

### Step 10: Access Application

Open your browser and visit: **http://localhost:8000**

You'll be redirected to the login page.

---

## 👤 Default Login Credentials

After seeding, you can login with these accounts:

### Super Admin
```
Email: admin@realestate.com
Password: password
```

### Manager
```
Email: manager@realestate.com
Password: password
```

### Dealer
```
Email: dealer@realestate.com
Password: password
```

### Accountant
```
Email: accountant@realestate.com
Password: password
```

### Staff
```
Email: staff@realestate.com
Password: password
```

**⚠️ IMPORTANT**: Change these passwords immediately in production!

---

## ✅ Post-Installation Verification

### 1. Test Authentication

```powershell
# Visit login page
start http://localhost:8000/login

# Try logging in with admin@realestate.com / password
```

### 2. Check Database

```powershell
# Connect to MySQL
mysql -u root -p realestatecrm

# Verify tables
SHOW TABLES;

# Should show 32 tables
# Check users
SELECT id, name, email FROM users;

# Should show 5 default users
```

### 3. Test Routes

```powershell
# List all routes
php artisan route:list

# Filter specific routes
php artisan route:list --name=dealers
php artisan route:list --name=reports
```

### 4. Clear Cache

```powershell
# Clear all caches
php artisan optimize:clear

# Or individually
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🗂️ Project Structure Overview

After installation, your project structure will be:

```
realestatecrm/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php ✅
│   │   │   │   └── RegisterController.php ✅
│   │   │   ├── DashboardController.php ✅
│   │   │   ├── SocietyController.php ✅
│   │   │   ├── BlockController.php ✅
│   │   │   ├── StreetController.php ✅
│   │   │   ├── PlotController.php ✅
│   │   │   ├── PropertyController.php ✅
│   │   │   ├── ClientController.php ✅
│   │   │   ├── LeadController.php ✅
│   │   │   ├── DealerController.php ✅ (NEW)
│   │   │   ├── DealController.php ✅
│   │   │   ├── PropertyFileController.php ✅
│   │   │   ├── PaymentController.php ✅
│   │   │   ├── AccountPaymentController.php ✅
│   │   │   ├── ExpenseController.php ✅
│   │   │   ├── FollowUpController.php ✅
│   │   │   └── ReportController.php ✅
│   │   └── Middleware/
│   │       ├── CheckRole.php ✅
│   │       └── CheckPermission.php ✅
│   └── Models/
│       ├── User.php ✅
│       ├── Role.php ✅
│       ├── Permission.php ✅
│       ├── Society.php ✅
│       ├── Block.php ✅
│       ├── Street.php ✅
│       ├── Plot.php ✅
│       ├── Property.php ✅
│       ├── Client.php ✅
│       ├── Lead.php ✅
│       ├── Dealer.php ✅
│       ├── Deal.php ✅
│       ├── PropertyFile.php ✅
│       ├── FilePayment.php ✅
│       ├── AccountPayment.php ✅
│       ├── Expense.php ✅
│       ├── PaymentType.php ✅
│       ├── Payment.php ✅ (legacy)
│       └── Installment.php ✅ (legacy)
├── database/
│   ├── migrations/ (32 files) ✅
│   └── seeders/
│       ├── RoleSeeder.php ✅
│       ├── PermissionSeeder.php ✅
│       ├── RolePermissionSeeder.php ✅
│       ├── UserSeeder.php ✅
│       └── DatabaseSeeder.php ✅
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php ✅ (NEW)
│       │   └── register.blade.php ✅ (NEW)
│       ├── dashboard/ ✅
│       ├── plots/ ✅
│       ├── clients/ ✅
│       ├── leads/ ✅
│       ├── payments/ ✅
│       └── reports/ ✅
├── routes/
│   ├── web.php ✅ (350+ routes)
│   └── auth.php ✅ (NEW)
└── Documentation/
    ├── PROJECT-STATUS.md ✅ (NEW)
    ├── SETUP-GUIDE.md ✅ (THIS FILE)
    ├── SYSTEM-SUMMARY.md ✅
    ├── AUTHENTICATION-SETUP.md ✅
    └── ... (32 more .md files)
```

---

## 🎯 What You Can Do Now

### 1. Login & Explore Dashboard

- Visit http://localhost:8000
- Login with admin credentials
- Explore the dashboard

### 2. Create Your First Society

```
Navigation: Dashboard → Societies → Add New

Example Data:
- Name: DHA Phase 5
- Code: DHA-5
- City: Lahore
- Status: Active
```

### 3. Add Blocks to Society

```
Navigation: Societies → View → Add Block

Example Data:
- Society: DHA Phase 5
- Block Name: A
- Block Code: BLK-A
- Status: Active
```

### 4. Add Streets to Block

```
Navigation: Blocks → View → Add Street

Example Data:
- Block: A
- Street Name: Street 1
- Street Code: ST-1
- Type: Residential
```

### 5. Add Plots to Street

```
Navigation: Streets → View → Add Plot

Example Data:
- Street: Street 1
- Plot Number: 123
- Size: 10 Marla
- Price: 5,000,000 PKR
- Status: Available
```

### 6. Create a Lead

```
Navigation: Dashboard → Leads → Add New

Example Data:
- Name: John Doe
- Phone: +92 300 1234567
- Email: john@example.com
- Source: Website
- Status: New
```

### 7. Convert Lead to Client

```
Navigation: Leads → View → Convert to Client

This will:
- Create a new client record
- Mark lead as converted
- Track conversion date
```

### 8. Create a Deal

```
Navigation: Deals → Add New

Example Data:
- Client: John Doe
- Property: Plot #123
- Dealer: Select a dealer
- Deal Amount: 5,000,000 PKR
- Commission: 2%
- Status: Pending
```

### 9. Create Property File

```
Navigation: Files → Add New

Example Data:
- Client: John Doe
- Property: Plot #123
- Total Amount: 5,000,000 PKR
- Down Payment: 500,000 PKR
- Installments: 36 months
- Installment Amount: 125,000 PKR
```

System will auto-generate 36 installments with due dates.

### 10. Record Payment

```
Navigation: Payments → Add New

Example Data:
- Property File: Select file
- Payment Type: Installment
- Amount: 125,000 PKR
- Payment Method: Bank Transfer
- Status: Received
```

### 11. View Reports

```
Navigation: Reports

Available Reports:
1. Available vs Sold Plots
2. Monthly Income
3. Dealer Commission
4. Overdue Installments
5. Society-wise Sales

Use filters to customize reports.
```

---

## 🔐 Security Considerations

### 1. Change Default Passwords

```powershell
# In production, immediately change default user passwords
# Login as each user and change password via profile
```

### 2. Update .env File

```env
# Set APP_ENV to production
APP_ENV=production

# Turn off debug mode
APP_DEBUG=false

# Use strong APP_KEY (already generated)
```

### 3. Configure Permissions

```powershell
# Set proper file permissions
# On Windows: Right-click folders → Properties → Security
# On Linux: chmod -R 755 storage bootstrap/cache
```

### 4. Setup HTTPS

- Use SSL certificate in production
- Update APP_URL to https://

---

## 🐛 Troubleshooting

### Issue 1: "SQLSTATE[HY000] [1045] Access denied"

**Solution**: Check database credentials in `.env`

```env
DB_USERNAME=root
DB_PASSWORD=your_actual_password
```

### Issue 2: "Class 'Spatie\Permission\PermissionServiceProvider' not found"

**Solution**: Install Spatie Permission package

```powershell
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### Issue 3: "Route [login] not defined"

**Solution**: Ensure auth routes are included

```php
// In routes/web.php
require __DIR__.'/auth.php';
```

### Issue 4: "SQLSTATE[42S02]: Base table or view not found"

**Solution**: Run migrations

```powershell
php artisan migrate:fresh --seed
```

⚠️ **Warning**: This will drop all tables and recreate them!

### Issue 5: "Class 'App\Models\Dealer' not found"

**Solution**: Check if Dealer model exists

```powershell
# If missing, check models directory
ls app\Models\Dealer.php
```

### Issue 6: Permission denied errors

**Solution**: Clear cache and regenerate

```powershell
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## 📚 Next Steps

### 1. Create Missing Views

Views needed:
- [ ] Society CRUD views
- [ ] Block CRUD views
- [ ] Street CRUD views
- [ ] Property CRUD views
- [ ] Deal CRUD views
- [ ] Dealer CRUD views
- [ ] File management views
- [ ] Expense views

### 2. Customize Design

- Edit existing views in `resources/views/`
- Customize CSS in `resources/css/app.css`
- Add JavaScript in `resources/js/app.js`

### 3. Add Your Logo

- Place logo in `public/images/logo.png`
- Update views to use your logo

### 4. Configure Email

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Setup Queue Worker

```powershell
# For email/notification processing
php artisan queue:work
```

---

## 📊 System Capabilities

### ✅ What's Working

- [x] User authentication (login/register/logout)
- [x] Role-based access control
- [x] Permission-based authorization
- [x] Society → Block → Street → Plot hierarchy
- [x] Lead management with follow-ups
- [x] Lead to client conversion
- [x] Client management
- [x] Dealer management
- [x] Deal lifecycle management
- [x] Property file creation
- [x] Installment generation
- [x] Payment recording (dual system)
- [x] Expense tracking
- [x] Comprehensive reporting (8 reports)
- [x] CSV export
- [x] Data validation
- [x] Database relationships

### ⏳ What Needs Work

- [ ] Complete frontend views
- [ ] File upload (property images)
- [ ] PDF generation (receipts)
- [ ] Email notifications
- [ ] SMS integration
- [ ] Calendar view
- [ ] Advanced search
- [ ] Audit logs
- [ ] API endpoints
- [ ] Mobile responsiveness
- [ ] Unit tests

---

## 💡 Tips for Success

### 1. Start Small

Begin with one complete flow:
```
Society → Block → Street → Plot → Lead → Client → Deal → File → Payment
```

### 2. Test Permissions

Login with different user roles to test access control.

### 3. Check Relationships

Use Tinker to verify model relationships:
```powershell
php artisan tinker

# Test relationships
>>> $plot = Plot::first();
>>> $plot->street->block->society->name;
>>> $deal = Deal::first();
>>> $deal->client->name;
>>> $deal->dealable; // Returns Plot or Property
```

### 4. Monitor Logs

```powershell
# Watch logs in real-time
Get-Content storage\logs\laravel.log -Wait

# Or use Laravel Pail
php artisan pail
```

### 5. Use Database Seeding

Create your own seeders for test data:
```powershell
php artisan make:seeder SocietyTestSeeder
```

---

## 🎉 You're Ready!

Your Real Estate CRM is now fully installed and ready to use!

### Quick Links

- **Login**: http://localhost:8000/login
- **Register**: http://localhost:8000/register
- **Dashboard**: http://localhost:8000/dashboard

### Support Resources

- **Documentation**: Check the 35 .md files in project root
- **Project Status**: Read `PROJECT-STATUS.md`
- **Module Guides**: Individual module documentation available

### Recommended Reading Order

1. `PROJECT-STATUS.md` - System overview
2. `AUTHENTICATION-SETUP.md` - Auth system
3. `MODULE-INTEGRATION-MAP.md` - How modules connect
4. Module-specific guides as needed

---

**Happy Building! 🚀**

If you encounter any issues, refer to the troubleshooting section or check the individual module documentation files.

---

**Last Updated**: January 29, 2026
**Version**: 1.0.0
**Status**: Ready for Development ✅
