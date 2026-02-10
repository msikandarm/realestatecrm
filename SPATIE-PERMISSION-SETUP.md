# Spatie Permission Setup - Complete Guide

## ✅ Installation Complete

Spatie Laravel Permission package has been successfully installed and configured throughout the entire project.

## 🔧 Changes Made

### 1. Package Installation
- **Package**: `spatie/laravel-permission` v6.24.0
- **Configuration**: Published to `config/permission.php`
- **Migrations**: Created permission tables (roles, permissions, model_has_roles, model_has_permissions, role_has_permissions)

### 2. Middleware Configuration (bootstrap/app.php)
**Old Custom Middleware (Removed):**
```php
'role' => \App\Http\Middleware\CheckRole::class,
'permission' => \App\Http\Middleware\CheckPermission::class,
```

**New Spatie Middleware:**
```php
'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
```

### 3. Models Updated

#### User Model (app/Models/User.php)
- Added `HasRoles` trait from Spatie
- Added `SoftDeletes` trait
- Helper methods already present:
  - `isAdmin()`, `isManager()`, `isDealer()`, `isAccountant()`, `isStaff()`

#### Removed Custom Models:
- ❌ `app/Models/Role.php` (using Spatie's model)
- ❌ `app/Models/Permission.php` (using Spatie's model)
- ❌ `app/Http/Middleware/CheckRole.php`
- ❌ `app/Http/Middleware/CheckPermission.php`

### 4. Database Seeders Updated

#### RoleSeeder
Creates 5 roles:
- `admin` - Full system access
- `manager` - Access to most features
- `dealer` - Sales agent permissions
- `accountant` - Financial management
- `staff` - Basic view permissions

#### PermissionSeeder
Created 60+ permissions across modules:
- **Societies**: view, create, edit, delete
- **Blocks**: view, create, edit, delete
- **Streets**: view, create, edit, delete
- **Plots**: view, create, edit, delete, assign
- **Properties**: view, create, edit, delete
- **Clients**: view, create, edit, delete, view_all
- **Leads**: view, create, edit, delete, convert, view_all
- **Deals**: view, create, edit, delete, approve, view_all
- **Files**: view, create, edit, delete, transfer
- **Payments**: view, create, edit, delete, view_all, approve
- **Expenses**: view, create, edit, delete, view_all, approve
- **Users**: view, create, edit, delete
- **Roles**: manage
- **Reports**: view, export, financial
- **Follow-ups**: view, create, edit, delete
- **Settings**: manage

#### RolePermissionSeeder
Assigns permissions to roles:

**Admin Role**: All permissions

**Manager Role**: All except:
- users.delete
- roles.manage
- settings.manage

**Dealer Role**:
- Leads: view, create, edit, convert
- Clients: view, create, edit
- Deals: view, create, edit
- Follow-ups: view, create, edit
- Properties/Plots: view only
- Societies/Blocks: view only
- Files: view only

**Accountant Role**:
- Payments: view, create, edit, view_all
- Expenses: view, create, edit, view_all
- Files: view, edit
- Clients: view, view_all
- Reports: view, export, financial
- Deals: view, view_all

**Staff Role**:
- View only: societies, blocks, plots, properties, clients, leads

### 5. Views Updated

All Blade views updated from policy-based to direct permissions:

#### Before (Policy-based):
```blade
@can('update', $property)
@can('delete', $property)
@can('create', App\Models\Property::class)
```

#### After (Direct Spatie Permissions):
```blade
@can('properties.edit')
@can('properties.delete')
@can('properties.create')
```

**Files Updated (30+):**
- properties/index.blade.php
- properties/show.blade.php
- plots/index.blade.php
- plots/show.blade.php
- deals/index.blade.php
- deals/show.blade.php
- files/index.blade.php
- files/show.blade.php
- expenses/index.blade.php
- expenses/show.blade.php
- dealers/index.blade.php
- dealers/show.blade.php
- clients/index.blade.php
- clients/show.blade.php
- leads/index.blade.php
- leads/show.blade.php
- payments/index.blade.php
- account-payments/index.blade.php
- And more...

### 6. Routes Configuration (routes/web.php)

Routes already properly configured with Spatie middleware:

```php
// Example Structure
Route::middleware(['permission:societies.view'])->group(function () {
    Route::get('societies', [SocietyController::class, 'index']);
    Route::get('societies/{society}', [SocietyController::class, 'show']);
});

Route::middleware(['permission:societies.create'])->group(function () {
    Route::get('societies/create', [SocietyController::class, 'create']);
    Route::post('societies', [SocietyController::class, 'store']);
});

Route::middleware(['permission:societies.edit'])->group(function () {
    Route::get('societies/{society}/edit', [SocietyController::class, 'edit']);
    Route::put('societies/{society}', [SocietyController::class, 'update']);
});

Route::middleware(['permission:societies.delete'])->group(function () {
    Route::delete('societies/{society}', [SocietyController::class, 'destroy']);
});
```

## 📊 Permission Structure

### Module-Based Permissions

| Module | Permissions |
|--------|------------|
| Societies | societies.view, societies.create, societies.edit, societies.delete |
| Blocks | blocks.view, blocks.create, blocks.edit, blocks.delete |
| Streets | streets.view, streets.create, streets.edit, streets.delete |
| Plots | plots.view, plots.create, plots.edit, plots.delete, plots.assign |
| Properties | properties.view, properties.create, properties.edit, properties.delete |
| Clients | clients.view, clients.create, clients.edit, clients.delete, clients.view_all |
| Leads | leads.view, leads.create, leads.edit, leads.delete, leads.convert, leads.view_all |
| Deals | deals.view, deals.create, deals.edit, deals.delete, deals.approve, deals.view_all |
| Files | files.view, files.create, files.edit, files.delete, files.transfer |
| Payments | payments.view, payments.create, payments.edit, payments.delete, payments.view_all, payments.approve |
| Expenses | expenses.view, expenses.create, expenses.edit, expenses.delete, expenses.view_all, expenses.approve |
| Users | users.view, users.create, users.edit, users.delete |
| Roles | roles.manage |
| Reports | reports.view, reports.export, reports.financial |
| Follow-ups | followups.view, followups.create, followups.edit, followups.delete |
| Settings | settings.manage |

## 🔐 Usage in Code

### In Controllers
```php
// Check permission
if (auth()->user()->can('societies.create')) {
    // User has permission
}

// Or use authorize
$this->authorize('societies.create');
```

### In Blade Views
```blade
@can('societies.create')
    <a href="{{ route('societies.create') }}" class="btn btn-primary">
        Add New Society
    </a>
@endcan

@cannot('societies.delete')
    <p>You don't have permission to delete</p>
@endcannot
```

### In Routes
```php
Route::middleware(['permission:societies.create'])->group(function () {
    // Routes that require this permission
});

// Or multiple permissions
Route::middleware(['permission:societies.create|societies.edit'])->group(function () {
    // Routes that require either permission
});
```

### Checking Roles
```php
// In Controller
if (auth()->user()->hasRole('admin')) {
    // User is admin
}

// In Blade
@role('admin')
    <p>Admin content</p>
@endrole

// Multiple roles
@hasanyrole('admin|manager')
    <p>Admin or Manager content</p>
@endhasanyrole
```

## 🗄️ Database Tables

### Spatie Permission Tables:
1. **roles** - Stores all roles
2. **permissions** - Stores all permissions
3. **model_has_roles** - Links users to roles
4. **model_has_permissions** - Direct user permissions
5. **role_has_permissions** - Links roles to permissions

## 📝 Useful Commands

```bash
# Clear permission cache
php artisan permission:cache-reset

# Re-seed permissions
php artisan db:seed --class=PermissionSeeder

# Re-seed role permissions
php artisan db:seed --class=RolePermissionSeeder

# Create new permission
php artisan tinker
>>> Spatie\Permission\Models\Permission::create(['name' => 'new.permission', 'guard_name' => 'web']);

# Assign permission to role
>>> $role = Spatie\Permission\Models\Role::where('name', 'admin')->first();
>>> $role->givePermissionTo('new.permission');

# Assign role to user
>>> $user = App\Models\User::find(1);
>>> $user->assignRole('admin');
```

## 🧪 Testing Permissions

```php
// Test in tinker
php artisan tinker

// Get user
$user = App\Models\User::find(1);

// Check role
$user->hasRole('admin');

// Check permission
$user->can('societies.create');

// Get all permissions
$user->getAllPermissions();

// Get role permissions
$role = Spatie\Permission\Models\Role::where('name', 'admin')->first();
$role->permissions;
```

## 🎯 Default Users

After seeding, these test users are available:

| Email | Password | Role | Permissions |
|-------|----------|------|-------------|
| admin@realestatecrm.com | password | admin | All permissions |
| manager@realestatecrm.com | password | manager | Most permissions (no user delete, roles manage, settings) |
| dealer@realestatecrm.com | password | dealer | Leads, clients, deals, follow-ups management |
| accountant@realestatecrm.com | password | accountant | Payments, expenses, reports |

## ✅ System Status

- ✅ Spatie package installed and configured
- ✅ Custom middleware removed
- ✅ Custom Role/Permission models removed
- ✅ Database seeded with roles and permissions
- ✅ All routes protected with permission middleware
- ✅ All views updated with @can directives
- ✅ User model configured with HasRoles trait
- ✅ Permission cache cleared

## 🚀 Next Steps

1. Test login with different user roles
2. Verify permissions work on all pages
3. Add more permissions as needed
4. Customize role permissions for your needs

## 📚 Documentation

For more information, visit:
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [GitHub Repository](https://github.com/spatie/laravel-permission)
