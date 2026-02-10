# Authentication System Implementation Guide

## Overview

This document describes the authentication and authorization system implemented for the Real Estate CRM using **Spatie Laravel Permission** package.

---

## 🔧 Installation Steps

### 1. Install Spatie Permission Package

```bash
composer require spatie/laravel-permission
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 3. Run Migrations

```bash
# This will create permission tables
php artisan migrate

# Run seeders to populate roles and permissions
php artisan db:seed
```

### 4. Clear Cache

```bash
php artisan optimize:clear
```

---

## 📁 Files Created

### Migrations
1. **`2014_10_12_000000_create_users_table.php`** - Users table with auth fields
2. **`2024_01_28_000001_add_description_to_roles_and_permissions.php`** - Extends roles/permissions tables

### Models
1. **`app/Models/User.php`** - Enhanced with HasRoles trait + relationships
2. **`app/Models/Role.php`** - Custom role model extending Spatie
3. **`app/Models/Permission.php`** - Custom permission model extending Spatie

### Middleware
1. **`app/Http/Middleware/CheckRole.php`** - Custom role verification
2. **`app/Http/Middleware/CheckPermission.php`** - Custom permission verification
3. **`bootstrap/app.php`** - Registered Spatie middleware aliases

### Seeders
1. **`database/seeders/PermissionSeeder.php`** - Creates 100+ permissions
2. **`database/seeders/RoleSeeder.php`** - Creates 6 roles
3. **`database/seeders/RolePermissionSeeder.php`** - Assigns permissions to roles
4. **`database/seeders/UserSeeder.php`** - Creates default users for each role
5. **`database/seeders/DatabaseSeeder.php`** - Updated to call all seeders

### Routes
1. **`routes/web.php`** - Protected routes with authentication + permissions

---

## 👥 Roles Defined

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| **Super Admin** | Full system access | All permissions (100+) |
| **Admin** | Administrative access | All except critical settings |
| **Manager** | Operations management | Approve deals, manage staff, reports |
| **Dealer** | Sales agent | Leads, clients, deals, own data |
| **Accountant** | Financial management | Payments, installments, financial reports |
| **Staff** | Basic operations | View data, create leads, follow-ups |

---

## 🔐 Permissions Structure

Permissions follow the pattern: `{module}.{action}`

### Modules
- `dashboard` - Dashboard access
- `users` - User management
- `roles` - Role management
- `societies` - Society management
- `blocks` - Block management
- `plots` - Plot management
- `properties` - Property management
- `leads` - Lead management
- `clients` - Client management
- `dealers` - Dealer management
- `deals` - Deal management
- `files` - Property file management
- `installments` - Installment management
- `payments` - Payment management
- `reports` - Reporting system
- `followups` - Follow-up management
- `settings` - System settings

### Actions
- `view_all` - View all records
- `view` - View single record (or own records only)
- `create` - Create new record
- `update` - Update record
- `delete` - Delete record
- `assign` - Assign to user/dealer
- `approve` - Approve (deals, expenses)
- `cancel` - Cancel (deals)
- `convert` - Convert lead to client
- `transfer` - Transfer file
- `waive` - Waive late fees
- `receive` - Receive payment
- `generate` - Generate reports

---

## 🔗 Relationships in User Model

```php
// User as Dealer (one-to-one)
$user->dealer

// Deals created by user
$user->createdDeals

// Deals approved by user
$user->approvedDeals

// Payments received by user
$user->receivedPayments

// Follow-ups assigned to user
$user->followUps
```

---

## 🛡️ Middleware Usage

### In Routes

```php
// Require authentication
Route::middleware(['auth'])->group(function () {
    // Routes here
});

// Require specific role
Route::middleware(['role:super-admin|admin'])->group(function () {
    // Admin routes
});

// Require specific permission
Route::middleware(['permission:reports.view'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
});

// Require role OR permission
Route::middleware(['role_or_permission:manager|deals.approve'])->group(function () {
    // Routes here
});
```

### In Controllers

```php
public function __construct()
{
    $this->middleware(['auth']);
    $this->middleware('permission:reports.view');
}

// OR in method
public function index()
{
    if (!auth()->user()->can('reports.view')) {
        abort(403);
    }
    // Logic here
}
```

---

## 🎨 Blade Directives

### Check Role

```blade
@role('admin')
    <p>Admin content</p>
@endrole

@hasanyrole('admin|manager')
    <p>Admin or Manager content</p>
@endhasanyrole
```

### Check Permission

```blade
@can('reports.view')
    <a href="{{ route('reports.index') }}">View Reports</a>
@endcan

@canany(['plots.create', 'properties.create'])
    <button>Add Inventory</button>
@endcanany
```

### Combined Usage

```blade
{{-- Show button only if user has permission --}}
@can('plots.create')
    <a href="{{ route('plots.create') }}" class="btn btn-primary">
        Create Plot
    </a>
@endcan

{{-- Hide for specific roles --}}
@unlessrole('staff')
    <div class="admin-panel">
        <!-- Admin content -->
    </div>
@endunlessrole
```

---

## 📊 Permission Matrix

| Permission | Super Admin | Admin | Manager | Dealer | Accountant | Staff |
|-----------|:-----------:|:-----:|:-------:|:------:|:----------:|:-----:|
| **Dashboard** |
| dashboard.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| dashboard.stats | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| **Users** |
| users.view_all | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| users.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| users.delete | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Plots** |
| plots.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| plots.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| plots.assign | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| **Leads** |
| leads.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| leads.create | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| leads.convert | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| **Deals** |
| deals.view_all | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| deals.create | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| deals.approve | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| **Payments** |
| payments.view_all | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| payments.create | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| **Reports** |
| reports.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| reports.commissions | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |

---

## 🧪 Default Users

After running seeders, these users are created:

| Email | Role | Password |
|-------|------|----------|
| superadmin@realestatecrm.com | Super Admin | password |
| admin@realestatecrm.com | Admin | password |
| manager@realestatecrm.com | Manager | password |
| dealer@realestatecrm.com | Dealer | password |
| accountant@realestatecrm.com | Accountant | password |
| staff@realestatecrm.com | Staff | password |

⚠️ **Change passwords immediately in production!**

---

## 🔄 Integration with Existing Modules

### ReportController
- Added `auth` middleware
- Added `permission:reports.view` middleware
- Only authorized users can access reports

### Routes (web.php)
- All routes now require authentication
- Dashboard requires `dashboard.view` permission
- Reports require `reports.view` permission
- Plots require `plots.view_all` permission
- Clients require `clients.view_all` permission
- Leads require `leads.view_all` permission
- Payments require `payments.view_all` permission

### Blade Views
- Use `@can`, `@role` directives to show/hide UI elements
- Example: Create button only shows if user has `plots.create` permission

---

## 📝 Usage Examples

### Check Permission in Controller

```php
public function store(Request $request)
{
    // Method 1: Using authorize helper
    $this->authorize('create', Plot::class);

    // Method 2: Manual check
    if (!auth()->user()->can('plots.create')) {
        abort(403);
    }

    // Method 3: Using gate
    if (Gate::denies('plots.create')) {
        abort(403);
    }

    // Create logic...
}
```

### Check Role in Controller

```php
public function index()
{
    // Show all deals for admin/manager
    if (auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager'])) {
        $deals = Deal::all();
    }
    // Show only own deals for dealers
    elseif (auth()->user()->hasRole('dealer')) {
        $deals = Deal::where('dealer_id', auth()->id())->get();
    }
    else {
        abort(403);
    }
}
```

### Assign Permissions

```php
// Give permission to user
$user->givePermissionTo('plots.create');

// Give multiple permissions
$user->givePermissionTo(['plots.create', 'plots.update', 'plots.delete']);

// Revoke permission
$user->revokePermissionTo('plots.delete');

// Sync permissions (remove all and add new)
$user->syncPermissions(['plots.view', 'plots.create']);

// Check permission
if ($user->hasPermissionTo('plots.create')) {
    // User can create plots
}
```

### Assign Roles

```php
// Assign role to user
$user->assignRole('dealer');

// Assign multiple roles
$user->assignRole(['dealer', 'accountant']);

// Remove role
$user->removeRole('dealer');

// Sync roles (remove all and assign new)
$user->syncRoles(['manager']);

// Check role
if ($user->hasRole('admin')) {
    // User is admin
}
```

---

## 🚀 Testing Authentication

### 1. Run Seeders
```bash
php artisan db:seed
```

### 2. Login as Different Users
- Visit `/login`
- Use credentials from default users table
- Test access to different routes

### 3. Test Permissions
```bash
# In tinker
php artisan tinker

# Get user
$user = User::where('email', 'dealer@realestatecrm.com')->first();

# Check permissions
$user->can('plots.create'); // false
$user->can('leads.create'); // true

# Get all permissions
$user->getAllPermissions();

# Get roles
$user->getRoleNames();
```

---

## 🔧 Artisan Commands

```bash
# Clear permission cache
php artisan permission:cache-reset

# Show all permissions
php artisan tinker
>>> Permission::all()->pluck('name');

# Show all roles with permissions
>>> Role::with('permissions')->get();

# Assign permission to role
>>> $role = Role::findByName('dealer');
>>> $role->givePermissionTo('new.permission');

# Sync permissions for role
>>> $role->syncPermissions(['perm1', 'perm2', 'perm3']);
```

---

## 🔐 Security Best Practices

1. **Always use HTTPS** in production
2. **Change default passwords** immediately
3. **Use strong passwords** (min 12 characters)
4. **Enable 2FA** for admin accounts (implement separately)
5. **Log permission changes** (audit trail)
6. **Regular security audits** of roles and permissions
7. **Principle of least privilege** - give minimum required permissions
8. **Review permissions quarterly**
9. **Revoke access immediately** for terminated employees
10. **Use environment variables** for sensitive data

---

## 📚 Additional Resources

- **Spatie Permission Docs**: https://spatie.be/docs/laravel-permission
- **Laravel Authorization**: https://laravel.com/docs/authorization
- **GitHub Repo**: https://github.com/spatie/laravel-permission

---

## ✅ Setup Checklist

- [x] Install Spatie Permission package
- [x] Publish configuration
- [x] Create users migration
- [x] Extend roles/permissions tables
- [x] Update User model with HasRoles trait
- [x] Create Role and Permission models
- [x] Create PermissionSeeder
- [x] Create RoleSeeder
- [x] Create RolePermissionSeeder
- [x] Create UserSeeder
- [x] Update DatabaseSeeder
- [x] Create custom middleware
- [x] Register middleware in bootstrap/app.php
- [x] Protect routes with middleware
- [x] Add permission checks to ReportController
- [x] Create web routes with authentication
- [ ] Run migrations: `php artisan migrate`
- [ ] Run seeders: `php artisan db:seed`
- [ ] Test authentication flow
- [ ] Change default passwords
- [ ] Add permission checks to all controllers
- [ ] Add @can directives to all views
- [ ] Create login/register views
- [ ] Configure email verification (optional)
- [ ] Set up password reset functionality

---

**Created**: January 28, 2026
**Laravel Version**: 11.x
**Spatie Permission**: v6.x
