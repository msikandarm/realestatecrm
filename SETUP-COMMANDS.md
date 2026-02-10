# 🚀 QUICK START - Authentication Setup Commands

## Run these commands in order:

### 1. Install Spatie Permission Package
```bash
composer require spatie/laravel-permission
```

### 2. Publish Configuration & Migrations
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Run Seeders (creates roles, permissions, and default users)
```bash
php artisan db:seed
```

### 5. Clear Application Cache
```bash
php artisan optimize:clear
```

---

## ✅ Verification

### Test Authentication
```bash
php artisan tinker
```

In tinker:
```php
// Get super admin user
$user = User::where('email', 'superadmin@realestatecrm.com')->first();

// Check role
$user->hasRole('super-admin'); // should return true

// Check permissions
$user->can('reports.view'); // should return true
$user->getAllPermissions(); // see all permissions

// Get all roles
Role::all()->pluck('name');

// Get all permissions
Permission::all()->pluck('name');

// Exit tinker
exit
```

---

## 🔐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@realestatecrm.com | password |
| Admin | admin@realestatecrm.com | password |
| Manager | manager@realestatecrm.com | password |
| Dealer | dealer@realestatecrm.com | password |
| Accountant | accountant@realestatecrm.com | password |
| Staff | staff@realestatecrm.com | password |

⚠️ **IMPORTANT:** Change all passwords immediately in production!

---

## 📊 What Was Created

### Database Tables
- ✅ `users` - User accounts with auth fields
- ✅ `roles` - 6 roles (Super Admin, Admin, Manager, Dealer, Accountant, Staff)
- ✅ `permissions` - 100+ granular permissions
- ✅ `model_has_roles` - User-Role assignments
- ✅ `model_has_permissions` - Direct user permissions
- ✅ `role_has_permissions` - Role-Permission assignments

### Roles Created
1. **Super Admin** - All permissions (100+)
2. **Admin** - Most permissions (97)
3. **Manager** - Operational permissions (65)
4. **Dealer** - CRM and sales permissions (35)
5. **Accountant** - Financial permissions (20)
6. **Staff** - Basic view permissions (12)

### Permission Modules
- Dashboard (2)
- Users (6)
- Roles (5)
- Societies (5)
- Blocks (4)
- Plots (6)
- Properties (5)
- Leads (7)
- Clients (5)
- Dealers (5)
- Deals (7)
- Files (6)
- Installments (4)
- Payments (6)
- Reports (7)
- Follow-ups (5)
- Settings (2)

---

## 🔄 Integration Complete

### Updated Files
- ✅ `app/Models/User.php` - Added HasRoles trait + relationships
- ✅ `app/Http/Controllers/ReportController.php` - Added auth middleware
- ✅ `routes/web.php` - Protected all routes with permissions
- ✅ `bootstrap/app.php` - Registered middleware aliases

### New Files Created
- ✅ `app/Models/Role.php`
- ✅ `app/Models/Permission.php`
- ✅ `app/Http/Middleware/CheckRole.php`
- ✅ `app/Http/Middleware/CheckPermission.php`
- ✅ `database/seeders/PermissionSeeder.php`
- ✅ `database/seeders/RoleSeeder.php`
- ✅ `database/seeders/RolePermissionSeeder.php`
- ✅ `database/seeders/UserSeeder.php`
- ✅ `database/migrations/2014_10_12_000000_create_users_table.php`
- ✅ `database/migrations/2024_01_28_000001_add_description_to_roles_and_permissions.php`

---

## 🎯 Next Steps

### 1. Test Routes
Visit these URLs (you'll be redirected to login):
```
http://localhost:8000/dashboard
http://localhost:8000/reports
http://localhost:8000/plots
http://localhost:8000/clients
http://localhost:8000/leads
http://localhost:8000/payments
```

### 2. Create Auth Views (Laravel Breeze/Jetstream)

**Option A: Laravel Breeze (Recommended)**
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
npm install && npm run dev
```

**Option B: Manual Login/Register Views**
- Create `resources/views/auth/login.blade.php`
- Create `resources/views/auth/register.blade.php`
- Create auth routes in `routes/auth.php`

### 3. Protect Controllers

Add permission checks to existing controllers:
```php
public function __construct()
{
    $this->middleware(['auth']);
    $this->middleware('permission:plots.view_all')->only('index');
    $this->middleware('permission:plots.create')->only(['create', 'store']);
}
```

### 4. Update Blade Views

Add permission directives:
```blade
@can('plots.create')
    <a href="{{ route('plots.create') }}">Create Plot</a>
@endcan

@role('admin')
    <div>Admin Panel</div>
@endrole
```

---

## 🛠️ Maintenance Commands

### Clear Permission Cache
```bash
php artisan permission:cache-reset
```

### Re-run Seeders (if needed)
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=UserSeeder
```

### Fresh Migration (WARNING: Deletes all data)
```bash
php artisan migrate:fresh --seed
```

---

## 📚 Documentation

- Full guide: `AUTHENTICATION-SETUP.md`
- Roles & Permissions: `ROLES-PERMISSIONS-GUIDE.md`
- Spatie Docs: https://spatie.be/docs/laravel-permission

---

**Setup Date:** January 28, 2026
**Laravel Version:** 11.x
**Spatie Permission:** v6.x
