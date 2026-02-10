# Quick Reference Guide - Real Estate CRM

## 🚀 Common Commands

### Development
```bash
# Start development server
php artisan serve

# Compile frontend assets (development)
npm run dev

# Compile frontend assets (production)
npm run build

# Watch for changes
npm run watch
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Refresh database (drops all tables and re-migrates)
php artisan migrate:fresh

# Refresh and seed
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=RoleSeeder
```

### Cache Management
```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan optimize:clear
```

### Utilities
```bash
# Create storage link
php artisan storage:link

# Generate application key
php artisan key:generate

# List all routes
php artisan route:list

# Interactive shell
php artisan tinker
```

---

## 🔐 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@realestatecrm.com | password |
| Manager | manager@realestatecrm.com | password |
| Dealer | dealer@realestatecrm.com | password |
| Accountant | accountant@realestatecrm.com | password |

**⚠️ Change these passwords immediately after first login!**

---

## 📝 Common Tasks in Tinker

### Change User Password
```php
php artisan tinker

$user = User::where('email', 'admin@realestatecrm.com')->first();
$user->password = Hash::make('new_secure_password');
$user->save();
```

### Create New User
```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123'),
    'role_id' => 3, // Dealer role
    'is_active' => true,
    'phone' => '+92-300-1234567',
]);
```

### Assign Permission to Role
```php
$role = Role::where('slug', 'dealer')->first();
$permission = Permission::where('slug', 'plots.view')->first();
$role->givePermission($permission);
```

### Check User Permissions
```php
$user = User::find(1);
$user->hasPermission('plots.create'); // true/false
$user->hasRole('admin'); // true/false
```

### Generate File Number
```php
PropertyFile::generateFileNumber(); // FILE-2026-00001
```

### Generate Deal Number
```php
Deal::generateDealNumber(); // DEAL-2026-0001
```

### Generate Receipt Number
```php
Payment::generateReceiptNumber(); // RCT-2026-000001
```

---

## 🗂️ File Locations

### Controllers
```
app/Http/Controllers/
├── DashboardController.php
├── SocietyController.php
├── PlotController.php
├── PropertyController.php
├── ClientController.php
├── LeadController.php
├── DealController.php
├── PropertyFileController.php
├── PaymentController.php
└── FollowUpController.php
```

### Models
```
app/Models/
├── User.php (extended)
├── Role.php
├── Permission.php
├── Society.php
├── Block.php
├── Street.php
├── Plot.php
├── Property.php
├── Client.php
├── Lead.php
├── FollowUp.php
├── Deal.php
├── PropertyFile.php
├── Installment.php
└── Payment.php
```

### Migrations
```
database/migrations/
├── 2026_01_28_000001_create_roles_table.php
├── 2026_01_28_000002_create_permissions_table.php
├── 2026_01_28_000003_create_role_permission_table.php
├── 2026_01_28_000004_add_role_to_users_table.php
├── 2026_01_28_000005_create_societies_table.php
├── 2026_01_28_000006_create_blocks_table.php
├── 2026_01_28_000007_create_streets_table.php
├── 2026_01_28_000008_create_plots_table.php
├── 2026_01_28_000009_create_properties_table.php
├── 2026_01_28_000010_create_clients_table.php
├── 2026_01_28_000011_create_leads_table.php
├── 2026_01_28_000012_create_follow_ups_table.php
├── 2026_01_28_000013_create_deals_table.php
├── 2026_01_28_000014_create_property_files_table.php
├── 2026_01_28_000015_create_installments_table.php
└── 2026_01_28_000016_create_payments_table.php
```

### Seeders
```
database/seeders/
├── DatabaseSeeder.php
├── RoleSeeder.php
├── PermissionSeeder.php
├── RolePermissionSeeder.php
└── UserSeeder.php
```

### Middleware
```
app/Http/Middleware/
├── CheckRole.php
└── CheckPermission.php
```

---

## 🔗 Route Prefixes

| Module | Prefix | Example |
|--------|--------|---------|
| Dashboard | /dashboard | /dashboard |
| Societies | /societies | /societies/1 |
| Plots | /plots | /plots/create |
| Properties | /properties | /properties/1/edit |
| Clients | /clients | /clients |
| Leads | /leads | /leads/1/convert |
| Deals | /deals | /deals/1/approve |
| Files | /files | /files/1/transfer |
| Payments | /payments | /payments/1/receipt |
| Follow-ups | /followups | /followups/1/complete |

---

## 🎯 Permission Slugs Reference

### Societies
- `societies.view`
- `societies.create`
- `societies.edit`
- `societies.delete`

### Plots
- `plots.view`
- `plots.create`
- `plots.edit`
- `plots.delete`
- `plots.assign`

### Properties
- `properties.view`
- `properties.create`
- `properties.edit`
- `properties.delete`

### Clients
- `clients.view`
- `clients.create`
- `clients.edit`
- `clients.delete`
- `clients.view_all`

### Leads
- `leads.view`
- `leads.create`
- `leads.edit`
- `leads.delete`
- `leads.convert`
- `leads.view_all`

### Deals
- `deals.view`
- `deals.create`
- `deals.edit`
- `deals.delete`
- `deals.approve`
- `deals.view_all`

### Files
- `files.view`
- `files.create`
- `files.edit`
- `files.delete`
- `files.transfer`

### Payments
- `payments.view`
- `payments.create`
- `payments.edit`
- `payments.delete`
- `payments.view_all`
- `payments.approve`

### Follow-ups
- `followups.view`
- `followups.create`
- `followups.edit`
- `followups.delete`

### System
- `users.view`
- `users.create`
- `users.edit`
- `users.delete`
- `roles.manage`
- `reports.view`
- `reports.export`
- `reports.financial`
- `settings.manage`

---

## 🔢 Unit Conversions

```php
// In controllers, use private method:
private function convertToSqft($size, $unit) {
    switch ($unit) {
        case 'marla':
            return $size * 272.25;
        case 'kanal':
            return $size * 5445;
        case 'sqft':
        default:
            return $size;
    }
}
```

**Conversion Reference:**
- 1 Marla = 272.25 sq ft
- 1 Kanal = 5,445 sq ft = 20 Marlas
- 1 Acre = 43,560 sq ft = 160 Marlas = 8 Kanals

---

## 🔍 Common Queries

### Get Available Plots
```php
Plot::available()->get();
// or
Plot::where('status', 'available')->get();
```

### Get Active Clients
```php
Client::active()->get();
// or
Client::where('client_status', 'active')->get();
```

### Get User's Assigned Leads
```php
Lead::where('assigned_to', Auth::id())->get();
```

### Get Overdue Installments
```php
Installment::overdue()->get();
```

### Get Today's Payments
```php
Payment::today()->get();
// or
Payment::whereDate('payment_date', today())->get();
```

### Get Pending Follow-ups
```php
FollowUp::pending()->where('assigned_to', Auth::id())->get();
```

---

## 📊 Dashboard Metrics Examples

### Calculate Revenue
```php
// Today
Payment::whereDate('payment_date', today())
    ->where('status', 'completed')
    ->sum('amount');

// This Month
Payment::whereMonth('payment_date', now()->month)
    ->whereYear('payment_date', now()->year)
    ->where('status', 'completed')
    ->sum('amount');
```

### Count Active Deals
```php
Deal::whereIn('status', ['pending', 'confirmed'])->count();
```

### Count Overdue Follow-ups
```php
FollowUp::where('status', 'pending')
    ->where('assigned_to', Auth::id())
    ->where('scheduled_at', '<', now())
    ->count();
```

---

## 🐛 Troubleshooting

### Issue: "Class not found" error
```bash
composer dump-autoload
```

### Issue: Routes not working
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Config changes not applying
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Views not updating
```bash
php artisan view:clear
```

### Issue: Permission denied on storage
```bash
# Windows (Run as Administrator in PowerShell)
icacls storage /grant Users:(OI)(CI)F /T
icacls bootstrap/cache /grant Users:(OI)(CI)F /T
```

### Issue: Database connection error
1. Check `.env` file for correct database credentials
2. Ensure MySQL service is running
3. Test connection:
```bash
php artisan tinker
DB::connection()->getPdo();
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| DOCUMENTATION.md | Complete system documentation (15,000+ words) |
| SYSTEM-SUMMARY.md | Quick overview and checklist |
| README-PROJECT.md | Quick start guide |
| QUICK-REFERENCE.md | This file - common commands and tasks |

---

## 🎓 Learning Resources

### Laravel Documentation
- https://laravel.com/docs/11.x

### Key Laravel Concepts Used
- Eloquent ORM & Relationships
- Migrations & Seeders
- Middleware
- Policies & Gates (for permissions)
- Soft Deletes
- Scopes
- Accessors & Mutators
- Polymorphic Relationships

### Blade Templates (for frontend)
- https://laravel.com/docs/11.x/blade

### Laravel Breeze (for authentication)
- https://laravel.com/docs/11.x/starter-kits#breeze

---

## ⚡ Quick Setup (One-Command)

### Windows (PowerShell)
```powershell
.\setup.ps1
```

### Manual Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configure database in .env
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

---

## 📞 Getting Help

1. Check **DOCUMENTATION.md** for detailed explanations
2. Review **SYSTEM-SUMMARY.md** for architecture overview
3. Search this file (QUICK-REFERENCE.md) for specific commands
4. Check Laravel documentation
5. Review controller code for implementation examples
6. Use `php artisan tinker` for quick testing

---

**Last Updated**: January 2026
**Version**: 1.0.0
**Laravel**: 11.x
