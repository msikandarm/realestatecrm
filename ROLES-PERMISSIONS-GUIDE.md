# Complete Role & Permission System - Real Estate CRM
## Using Spatie Laravel Permission Package

---

## 📋 Table of Contents
1. [Installation](#installation)
2. [Migration Structure](#migration-structure)
3. [Model Setup](#model-setup)
4. [Seeders](#seeders)
5. [Middleware](#middleware)
6. [Usage in Controllers](#usage-in-controllers)
7. [Usage in Blade Templates](#usage-in-blade-templates)
8. [Usage in Routes](#usage-in-routes)
9. [Advanced Features](#advanced-features)
10. [Complete Examples](#complete-examples)

---

## 🚀 1. INSTALLATION

### Step 1: Install Spatie Permission Package

```bash
composer require spatie/laravel-permission
```

### Step 2: Publish Configuration & Migrations

```bash
# Publish config file
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate
```

### Step 3: Clear Cache

```bash
php artisan optimize:clear
# or
php artisan config:clear
php artisan cache:clear
```

---

## 🗄️ 2. MIGRATION STRUCTURE

### Generated Migrations (from Spatie)

The package automatically creates these tables:

```php
// database/migrations/xxxx_create_permission_tables.php

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded.');
        }

        // 1. permissions table
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');       // e.g., 'create_property'
            $table->string('guard_name'); // e.g., 'web'
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // 2. roles table
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');       // e.g., 'admin'
            $table->string('guard_name'); // e.g., 'web'
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // 3. model_has_permissions table (pivot)
        // Direct permissions assigned to models (users)
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
        });

        // 4. model_has_roles table (pivot)
        // Roles assigned to models (users)
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
        });

        // 5. role_has_permissions table (pivot)
        // Permissions assigned to roles
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, PermissionRegistrar::$pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
```

### Custom Migration (Optional - Add Description to Roles/Permissions)

```php
// database/migrations/2026_01_28_000001_add_description_to_roles_and_permissions.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('description');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable()->after('name')->comment('Module: properties, plots, users, etc.');
            $table->string('description')->nullable()->after('module');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['module', 'description']);
        });
    }
};
```

---

## 🎭 3. MODEL SETUP

### User Model

```php
// app/Models/User.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'cnic',
        'address',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Custom helper methods
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isDealer(): bool
    {
        return $this->hasRole('dealer');
    }

    public function isAccountant(): bool
    {
        return $this->hasRole('accountant');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    // Check if user can access admin panel
    public function canAccessAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'manager']);
    }
}
```

### Optional: Custom Role Model

```php
// app/Models/Role.php (Optional - Extends Spatie's Role)

<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Get only active roles
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get role with permissions count
    public function scopeWithPermissionsCount($query)
    {
        return $query->withCount('permissions');
    }
}
```

### Optional: Custom Permission Model

```php
// app/Models/Permission.php (Optional - Extends Spatie's Permission)

<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'module',
        'description',
    ];

    // Get permissions by module
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    // Get all modules
    public static function getModules(): array
    {
        return self::distinct('module')
            ->whereNotNull('module')
            ->pluck('module')
            ->toArray();
    }
}
```

### Update config/permission.php (Optional)

```php
// config/permission.php

return [
    'models' => [
        'permission' => App\Models\Permission::class,
        'role' => App\Models\Role::class,
    ],

    // ... rest of config
];
```

---

## 🌱 4. SEEDERS

### Complete Permission Seeder

```php
// database/seeders/PermissionSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User Management
            ['name' => 'users.view_all', 'module' => 'users', 'description' => 'View all users'],
            ['name' => 'users.view', 'module' => 'users', 'description' => 'View single user'],
            ['name' => 'users.create', 'module' => 'users', 'description' => 'Create new user'],
            ['name' => 'users.update', 'module' => 'users', 'description' => 'Update user'],
            ['name' => 'users.delete', 'module' => 'users', 'description' => 'Delete user'],
            ['name' => 'users.assign_role', 'module' => 'users', 'description' => 'Assign roles to users'],

            // Role Management
            ['name' => 'roles.view_all', 'module' => 'roles', 'description' => 'View all roles'],
            ['name' => 'roles.create', 'module' => 'roles', 'description' => 'Create new role'],
            ['name' => 'roles.update', 'module' => 'roles', 'description' => 'Update role'],
            ['name' => 'roles.delete', 'module' => 'roles', 'description' => 'Delete role'],
            ['name' => 'roles.assign_permission', 'module' => 'roles', 'description' => 'Assign permissions to roles'],

            // Society Management
            ['name' => 'societies.view_all', 'module' => 'societies', 'description' => 'View all societies'],
            ['name' => 'societies.view', 'module' => 'societies', 'description' => 'View single society'],
            ['name' => 'societies.create', 'module' => 'societies', 'description' => 'Create new society'],
            ['name' => 'societies.update', 'module' => 'societies', 'description' => 'Update society'],
            ['name' => 'societies.delete', 'module' => 'societies', 'description' => 'Delete society'],

            // Block Management
            ['name' => 'blocks.view_all', 'module' => 'blocks', 'description' => 'View all blocks'],
            ['name' => 'blocks.create', 'module' => 'blocks', 'description' => 'Create new block'],
            ['name' => 'blocks.update', 'module' => 'blocks', 'description' => 'Update block'],
            ['name' => 'blocks.delete', 'module' => 'blocks', 'description' => 'Delete block'],

            // Plot Management
            ['name' => 'plots.view_all', 'module' => 'plots', 'description' => 'View all plots'],
            ['name' => 'plots.view', 'module' => 'plots', 'description' => 'View single plot'],
            ['name' => 'plots.create', 'module' => 'plots', 'description' => 'Create new plot'],
            ['name' => 'plots.update', 'module' => 'plots', 'description' => 'Update plot'],
            ['name' => 'plots.delete', 'module' => 'plots', 'description' => 'Delete plot'],
            ['name' => 'plots.assign', 'module' => 'plots', 'description' => 'Assign plot to client'],

            // Property Management
            ['name' => 'properties.view_all', 'module' => 'properties', 'description' => 'View all properties'],
            ['name' => 'properties.view', 'module' => 'properties', 'description' => 'View single property'],
            ['name' => 'properties.create', 'module' => 'properties', 'description' => 'Create new property'],
            ['name' => 'properties.update', 'module' => 'properties', 'description' => 'Update property'],
            ['name' => 'properties.delete', 'module' => 'properties', 'description' => 'Delete property'],

            // Lead Management
            ['name' => 'leads.view_all', 'module' => 'leads', 'description' => 'View all leads'],
            ['name' => 'leads.view', 'module' => 'leads', 'description' => 'View own leads only'],
            ['name' => 'leads.create', 'module' => 'leads', 'description' => 'Create new lead'],
            ['name' => 'leads.update', 'module' => 'leads', 'description' => 'Update lead'],
            ['name' => 'leads.delete', 'module' => 'leads', 'description' => 'Delete lead'],
            ['name' => 'leads.assign', 'module' => 'leads', 'description' => 'Assign lead to dealer'],
            ['name' => 'leads.convert', 'module' => 'leads', 'description' => 'Convert lead to client'],

            // Client Management
            ['name' => 'clients.view_all', 'module' => 'clients', 'description' => 'View all clients'],
            ['name' => 'clients.view', 'module' => 'clients', 'description' => 'View own clients only'],
            ['name' => 'clients.create', 'module' => 'clients', 'description' => 'Create new client'],
            ['name' => 'clients.update', 'module' => 'clients', 'description' => 'Update client'],
            ['name' => 'clients.delete', 'module' => 'clients', 'description' => 'Delete client'],

            // Deal Management
            ['name' => 'deals.view_all', 'module' => 'deals', 'description' => 'View all deals'],
            ['name' => 'deals.view', 'module' => 'deals', 'description' => 'View own deals only'],
            ['name' => 'deals.create', 'module' => 'deals', 'description' => 'Create new deal'],
            ['name' => 'deals.update', 'module' => 'deals', 'description' => 'Update deal'],
            ['name' => 'deals.delete', 'module' => 'deals', 'description' => 'Delete deal'],
            ['name' => 'deals.approve', 'module' => 'deals', 'description' => 'Approve deal'],
            ['name' => 'deals.cancel', 'module' => 'deals', 'description' => 'Cancel deal'],

            // File Management
            ['name' => 'files.view_all', 'module' => 'files', 'description' => 'View all property files'],
            ['name' => 'files.view', 'module' => 'files', 'description' => 'View own files only'],
            ['name' => 'files.create', 'module' => 'files', 'description' => 'Create new file'],
            ['name' => 'files.update', 'module' => 'files', 'description' => 'Update file'],
            ['name' => 'files.delete', 'module' => 'files', 'description' => 'Delete file'],
            ['name' => 'files.transfer', 'module' => 'files', 'description' => 'Transfer file to another client'],

            // Installment Management
            ['name' => 'installments.view', 'module' => 'installments', 'description' => 'View installments'],
            ['name' => 'installments.create', 'module' => 'installments', 'description' => 'Create installments'],
            ['name' => 'installments.update', 'module' => 'installments', 'description' => 'Update installments'],
            ['name' => 'installments.waive', 'module' => 'installments', 'description' => 'Waive late fees'],

            // Payment Management
            ['name' => 'payments.view_all', 'module' => 'payments', 'description' => 'View all payments'],
            ['name' => 'payments.view', 'module' => 'payments', 'description' => 'View payments'],
            ['name' => 'payments.create', 'module' => 'payments', 'description' => 'Record new payment'],
            ['name' => 'payments.update', 'module' => 'payments', 'description' => 'Update payment'],
            ['name' => 'payments.delete', 'module' => 'payments', 'description' => 'Delete payment'],
            ['name' => 'payments.receive', 'module' => 'payments', 'description' => 'Receive payment'],

            // Expense Management
            ['name' => 'expenses.view_all', 'module' => 'expenses', 'description' => 'View all expenses'],
            ['name' => 'expenses.create', 'module' => 'expenses', 'description' => 'Create expense'],
            ['name' => 'expenses.update', 'module' => 'expenses', 'description' => 'Update expense'],
            ['name' => 'expenses.delete', 'module' => 'expenses', 'description' => 'Delete expense'],
            ['name' => 'expenses.approve', 'module' => 'expenses', 'description' => 'Approve expense'],

            // Report Management
            ['name' => 'reports.view', 'module' => 'reports', 'description' => 'View reports'],
            ['name' => 'reports.generate', 'module' => 'reports', 'description' => 'Generate reports'],
            ['name' => 'reports.sales', 'module' => 'reports', 'description' => 'View sales reports'],
            ['name' => 'reports.revenue', 'module' => 'reports', 'description' => 'View revenue reports'],
            ['name' => 'reports.commission', 'module' => 'reports', 'description' => 'View commission reports'],
            ['name' => 'reports.financial', 'module' => 'reports', 'description' => 'View financial reports'],

            // Follow-up Management
            ['name' => 'followups.view_all', 'module' => 'followups', 'description' => 'View all follow-ups'],
            ['name' => 'followups.view', 'module' => 'followups', 'description' => 'View own follow-ups'],
            ['name' => 'followups.create', 'module' => 'followups', 'description' => 'Create follow-up'],
            ['name' => 'followups.update', 'module' => 'followups', 'description' => 'Update follow-up'],
            ['name' => 'followups.delete', 'module' => 'followups', 'description' => 'Delete follow-up'],

            // Settings
            ['name' => 'settings.view', 'module' => 'settings', 'description' => 'View settings'],
            ['name' => 'settings.update', 'module' => 'settings', 'description' => 'Update settings'],

            // Dashboard
            ['name' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'View dashboard'],
            ['name' => 'dashboard.stats', 'module' => 'dashboard', 'description' => 'View statistics'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'module' => $permission['module'] ?? null,
                    'description' => $permission['description'] ?? null,
                    'guard_name' => 'web'
                ]
            );
        }

        $this->command->info('Permissions created successfully!');
    }
}
```

### Complete Role Seeder

```php
// database/seeders/RoleSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            [
                'name' => 'super-admin',
                'description' => 'Super Administrator - Full system access',
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator - Manage most system features',
                'is_active' => true,
            ],
            [
                'name' => 'manager',
                'description' => 'Manager - Oversee operations and approve deals',
                'is_active' => true,
            ],
            [
                'name' => 'dealer',
                'description' => 'Dealer/Agent - Manage leads, clients, and deals',
                'is_active' => true,
            ],
            [
                'name' => 'accountant',
                'description' => 'Accountant - Manage payments and financial records',
                'is_active' => true,
            ],
            [
                'name' => 'staff',
                'description' => 'Staff - Limited access to basic features',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'is_active' => $role['is_active'],
                    'guard_name' => 'web'
                ]
            );
        }

        $this->command->info('Roles created successfully!');
    }
}
```

### Role-Permission Assignment Seeder

```php
// database/seeders/RolePermissionSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Super Admin - All Permissions
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most Permissions (exclude super admin features)
        $admin = Role::findByName('admin');
        $adminPermissions = Permission::whereNotIn('name', [
            'settings.update',
            'users.delete',
        ])->get();
        $admin->givePermissionTo($adminPermissions);

        // Manager - Approval & Oversight Permissions
        $manager = Role::findByName('manager');
        $managerPermissions = [
            // Dashboard
            'dashboard.view', 'dashboard.stats',

            // View all
            'societies.view_all', 'societies.view',
            'blocks.view_all',
            'plots.view_all', 'plots.view',
            'properties.view_all', 'properties.view',
            'leads.view_all', 'leads.view',
            'clients.view_all', 'clients.view',
            'deals.view_all', 'deals.view',
            'files.view_all', 'files.view',
            'payments.view_all', 'payments.view',
            'followups.view_all', 'followups.view',

            // Society & Plot Management
            'societies.create', 'societies.update',
            'blocks.create', 'blocks.update',
            'plots.create', 'plots.update', 'plots.assign',
            'properties.create', 'properties.update',

            // Lead & Client Management
            'leads.create', 'leads.update', 'leads.assign', 'leads.convert',
            'clients.create', 'clients.update',

            // Deal Management
            'deals.create', 'deals.update', 'deals.approve', 'deals.cancel',

            // File & Payment Management
            'files.create', 'files.update', 'files.transfer',
            'installments.view', 'installments.update', 'installments.waive',
            'payments.create', 'payments.receive',

            // Follow-ups
            'followups.create', 'followups.update',

            // Reports
            'reports.view', 'reports.generate', 'reports.sales', 'reports.revenue', 'reports.commission',

            // Expenses
            'expenses.view_all', 'expenses.approve',
        ];
        $manager->givePermissionTo($managerPermissions);

        // Dealer - CRM & Sales Permissions
        $dealer = Role::findByName('dealer');
        $dealerPermissions = [
            // Dashboard
            'dashboard.view', 'dashboard.stats',

            // View (limited to own records)
            'societies.view_all', 'societies.view',
            'blocks.view_all',
            'plots.view_all', 'plots.view',
            'properties.view_all', 'properties.view',
            'leads.view', 'leads.view_all', // Can see all leads for prospecting
            'clients.view', // Only own clients
            'deals.view', // Only own deals
            'files.view', // Only own files
            'payments.view',
            'followups.view',

            // Lead Management
            'leads.create', 'leads.update', 'leads.convert',

            // Client Management
            'clients.create', 'clients.update',

            // Deal Management
            'deals.create', 'deals.update',

            // File Management
            'files.create', 'files.update',
            'installments.view',
            'payments.create', 'payments.receive',

            // Follow-ups
            'followups.create', 'followups.update', 'followups.delete',

            // Reports (own performance)
            'reports.view',
        ];
        $dealer->givePermissionTo($dealerPermissions);

        // Accountant - Financial Permissions
        $accountant = Role::findByName('accountant');
        $accountantPermissions = [
            // Dashboard
            'dashboard.view', 'dashboard.stats',

            // View (financial focus)
            'clients.view_all', 'clients.view',
            'deals.view_all', 'deals.view',
            'files.view_all', 'files.view',
            'payments.view_all', 'payments.view',
            'expenses.view_all',

            // Payment Management
            'payments.create', 'payments.update', 'payments.receive',

            // Installment Management
            'installments.view', 'installments.update',

            // Expense Management
            'expenses.create', 'expenses.update',

            // File Management (payment related)
            'files.view_all', 'files.update',

            // Reports (financial)
            'reports.view', 'reports.generate', 'reports.revenue', 'reports.financial', 'reports.commission',
        ];
        $accountant->givePermissionTo($accountantPermissions);

        // Staff - Basic View Permissions
        $staff = Role::findByName('staff');
        $staffPermissions = [
            // Dashboard
            'dashboard.view',

            // View Only
            'societies.view_all', 'societies.view',
            'blocks.view_all',
            'plots.view_all', 'plots.view',
            'properties.view_all', 'properties.view',
            'leads.view',
            'clients.view',
            'followups.view',

            // Basic Operations
            'leads.create',
            'followups.create', 'followups.update',
        ];
        $staff->givePermissionTo($staffPermissions);

        $this->command->info('Role-Permission assignments completed successfully!');
    }
}
```

### User Seeder with Roles

```php
// database/seeders/UserSeeder.php

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@realestatecrm.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '03001234567',
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@realestatecrm.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '03001234568',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@realestatecrm.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'phone' => '03001234569',
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Dealer
        $dealer = User::firstOrCreate(
            ['email' => 'dealer@realestatecrm.com'],
            [
                'name' => 'Dealer User',
                'password' => Hash::make('password'),
                'phone' => '03001234570',
                'is_active' => true,
            ]
        );
        $dealer->assignRole('dealer');

        // Accountant
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@realestatecrm.com'],
            [
                'name' => 'Accountant User',
                'password' => Hash::make('password'),
                'phone' => '03001234571',
                'is_active' => true,
            ]
        );
        $accountant->assignRole('accountant');

        // Staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@realestatecrm.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'phone' => '03001234572',
                'is_active' => true,
            ]
        );
        $staff->assignRole('staff');

        $this->command->info('Users created and roles assigned successfully!');
    }
}
```

### Update DatabaseSeeder

```php
// database/seeders/DatabaseSeeder.php

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order is important!
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
```

---

## 🛡️ 5. MIDDLEWARE

### Method 1: Using Built-in Middleware

Spatie package provides built-in middleware:

```php
// app/Http/Kernel.php or bootstrap/app.php (Laravel 11)

protected $middlewareAliases = [
    // Spatie Permission Middleware
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
];
```

### Method 2: Custom Middleware (Laravel 11)

```php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->create();
```

### Custom Permission Middleware (Optional - More Control)

```php
// app/Http/Middleware/CheckPermission.php

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to access this resource. Required: {$permission}");
        }

        return $next($request);
    }
}
```

### Custom Role Middleware (Optional)

```php
// app/Http/Middleware/CheckRole.php

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
```

---

## 🎮 6. USAGE IN CONTROLLERS

### Example 1: Plot Controller

```php
// app/Http/Controllers/PlotController.php

<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function __construct()
    {
        // Apply middleware to all methods
        $this->middleware(['auth']);

        // Apply specific permission middleware
        $this->middleware('permission:plots.view_all')->only(['index']);
        $this->middleware('permission:plots.create')->only(['create', 'store']);
        $this->middleware('permission:plots.update')->only(['edit', 'update']);
        $this->middleware('permission:plots.delete')->only(['destroy']);
    }

    public function index()
    {
        // Check permission in method (alternative)
        if (!auth()->user()->can('plots.view_all')) {
            abort(403);
        }

        $plots = Plot::with('society', 'block')->paginate(20);
        return view('plots.index', compact('plots'));
    }

    public function create()
    {
        // Check using helper method
        $this->authorize('create', Plot::class);

        return view('plots.create');
    }

    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermissionTo('plots.create')) {
            return back()->with('error', 'You do not have permission to create plots.');
        }

        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'plot_number' => 'required|string',
            // ... other fields
        ]);

        $plot = Plot::create($validated);

        return redirect()->route('plots.index')
            ->with('success', 'Plot created successfully!');
    }

    public function edit(Plot $plot)
    {
        // Check using gate
        if (!auth()->user()->can('plots.update')) {
            abort(403, 'You cannot edit this plot.');
        }

        return view('plots.edit', compact('plot'));
    }

    public function destroy(Plot $plot)
    {
        // Multiple permission check
        if (!auth()->user()->hasAnyPermission(['plots.delete', 'super-admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $plot->delete();

        return redirect()->route('plots.index')
            ->with('success', 'Plot deleted successfully!');
    }

    public function assign(Request $request, Plot $plot)
    {
        // Check specific permission
        $this->authorize('assign', $plot);
        // OR
        if (!auth()->user()->can('plots.assign')) {
            abort(403);
        }

        // Assignment logic...
    }
}
```

### Example 2: Deal Controller with Role Checks

```php
// app/Http/Controllers/DealController.php

<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        // Show all deals for admin/manager
        if (auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            $deals = Deal::with('client', 'dealer')->latest()->paginate(20);
        }
        // Show only own deals for dealers
        elseif (auth()->user()->hasRole('dealer')) {
            $deals = Deal::where('dealer_id', auth()->id())
                ->with('client')
                ->latest()
                ->paginate(20);
        }
        else {
            abort(403, 'You do not have permission to view deals.');
        }

        return view('deals.index', compact('deals'));
    }

    public function approve(Deal $deal)
    {
        // Only managers and admins can approve
        if (!auth()->user()->hasAnyRole(['manager', 'admin', 'super-admin'])) {
            return back()->with('error', 'Only managers can approve deals.');
        }

        // OR using permission
        if (!auth()->user()->can('deals.approve')) {
            abort(403);
        }

        $deal->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Deal approved successfully!');
    }

    public function store(Request $request)
    {
        // Check if user can create deals
        abort_unless(auth()->user()->can('deals.create'), 403);

        // Create deal logic...
    }
}
```

### Example 3: Report Controller

```php
// app/Http/Controllers/ReportController.php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales()
    {
        // Check permission
        if (!auth()->user()->can('reports.sales')) {
            abort(403, 'You do not have permission to view sales reports.');
        }

        // Generate sales report...
    }

    public function financial()
    {
        // Only accountants, managers, and admins
        if (!auth()->user()->hasAnyPermission(['reports.financial', 'accountant', 'manager', 'admin'])) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied to financial reports.');
        }

        // Generate financial report...
    }
}
```

### Example 4: Using Gates

```php
// app/Providers/AuthServiceProvider.php

<?php

namespace App\Providers;

use App\Models\Plot;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Define gates
        Gate::define('assign-plot', function ($user, Plot $plot) {
            return $user->hasPermissionTo('plots.assign') && $plot->status === 'available';
        });

        Gate::define('approve-deal', function ($user) {
            return $user->hasAnyRole(['manager', 'admin']);
        });

        // Super admin bypass
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}

// Usage in Controller
public function assignPlot(Plot $plot)
{
    if (Gate::denies('assign-plot', $plot)) {
        abort(403);
    }

    // Assignment logic...
}
```

---

## 🎨 7. USAGE IN BLADE TEMPLATES

### Example 1: Conditional Display Based on Permissions

```blade
{{-- resources/views/plots/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Plots</h1>

        {{-- Show create button only if user has permission --}}
        @can('plots.create')
            <a href="{{ route('plots.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Plot
            </a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Plot Number</th>
                    <th>Society</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plots as $plot)
                <tr>
                    <td>{{ $plot->plot_number }}</td>
                    <td>{{ $plot->society->name }}</td>
                    <td>{{ $plot->size_marla }} Marla</td>
                    <td>
                        <span class="badge bg-{{ $plot->status === 'available' ? 'success' : 'warning' }}">
                            {{ ucfirst($plot->status) }}
                        </span>
                    </td>
                    <td>
                        {{-- View button (everyone with plots.view) --}}
                        @can('plots.view')
                            <a href="{{ route('plots.show', $plot) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endcan

                        {{-- Edit button (only with plots.update permission) --}}
                        @can('plots.update')
                            <a href="{{ route('plots.edit', $plot) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan

                        {{-- Assign button (only with plots.assign permission) --}}
                        @can('plots.assign')
                            @if($plot->status === 'available')
                                <button class="btn btn-sm btn-success" onclick="assignPlot({{ $plot->id }})">
                                    <i class="fas fa-user-plus"></i> Assign
                                </button>
                            @endif
                        @endcan

                        {{-- Delete button (only with plots.delete permission) --}}
                        @can('plots.delete')
                            <form action="{{ route('plots.destroy', $plot) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Example 2: Role-Based Display

```blade
{{-- resources/views/dashboard.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard</h1>

    {{-- Super Admin Section --}}
    @role('super-admin')
        <div class="alert alert-info">
            <h4>Super Admin Controls</h4>
            <p>You have full system access.</p>
            <a href="{{ route('settings.index') }}" class="btn btn-primary">System Settings</a>
        </div>
    @endrole

    {{-- Admin & Manager Section --}}
    @hasanyrole('admin|manager')
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Plots</h5>
                        <h2>{{ $stats['total_plots'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Properties</h5>
                        <h2>{{ $stats['total_properties'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    @endhasanyrole

    {{-- Dealer Section --}}
    @role('dealer')
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>My Leads</h5>
                        <h2>{{ $myStats['leads'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>My Clients</h5>
                        <h2>{{ $myStats['clients'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>My Deals</h5>
                        <h2>{{ $myStats['deals'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    @endrole

    {{-- Accountant Section --}}
    @role('accountant')
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Today's Revenue</h5>
                        <h2>Rs. {{ number_format($revenue['today']) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Pending Payments</h5>
                        <h2>Rs. {{ number_format($revenue['pending']) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    @endrole

    {{-- Common Section for All Roles --}}
    @hasrole('dealer|accountant|staff')
        <div class="alert alert-warning mt-3">
            <p>Contact your manager for additional access.</p>
        </div>
    @endhasrole
</div>
@endsection
```

### Example 3: Navigation Menu with Permissions

```blade
{{-- resources/views/layouts/sidebar.blade.php --}}

<aside class="sidebar">
    <ul class="nav flex-column">
        {{-- Dashboard (everyone) --}}
        @can('dashboard.view')
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
        @endcan

        {{-- Societies (admin, manager) --}}
        @can('societies.view_all')
            <li class="nav-item">
                <a href="{{ route('societies.index') }}" class="nav-link">
                    <i class="fas fa-building"></i> Societies
                </a>
            </li>
        @endcan

        {{-- Plots (admin, manager, dealer) --}}
        @canany(['plots.view_all', 'plots.view'])
            <li class="nav-item">
                <a href="{{ route('plots.index') }}" class="nav-link">
                    <i class="fas fa-map"></i> Plots
                </a>
            </li>
        @endcanany

        {{-- Properties --}}
        @can('properties.view_all')
            <li class="nav-item">
                <a href="{{ route('properties.index') }}" class="nav-link">
                    <i class="fas fa-home"></i> Properties
                </a>
            </li>
        @endcan

        {{-- Leads & Clients (CRM) --}}
        @canany(['leads.view', 'clients.view'])
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#crmMenu">
                    <i class="fas fa-users"></i> CRM
                </a>
                <ul id="crmMenu" class="collapse">
                    @can('leads.view')
                        <li><a href="{{ route('leads.index') }}">Leads</a></li>
                    @endcan
                    @can('clients.view')
                        <li><a href="{{ route('clients.index') }}">Clients</a></li>
                    @endcan
                    @can('deals.view')
                        <li><a href="{{ route('deals.index') }}">Deals</a></li>
                    @endcan
                    @can('followups.view')
                        <li><a href="{{ route('followups.index') }}">Follow-ups</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Files & Payments (accountant, manager) --}}
        @canany(['files.view_all', 'payments.view_all'])
            <li class="nav-item">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="collapse" data-target="#financeMenu">
                    <i class="fas fa-dollar-sign"></i> Finance
                </a>
                <ul id="financeMenu" class="collapse">
                    @can('files.view_all')
                        <li><a href="{{ route('files.index') }}">Files</a></li>
                    @endcan
                    @can('payments.view_all')
                        <li><a href="{{ route('payments.index') }}">Payments</a></li>
                    @endcan
                    @can('expenses.view_all')
                        <li><a href="{{ route('expenses.index') }}">Expenses</a></li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Reports --}}
        @can('reports.view')
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
        @endcan

        {{-- User Management (admin only) --}}
        @can('users.view_all')
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link">
                    <i class="fas fa-user-cog"></i> Users
                </a>
            </li>
        @endcan

        {{-- Settings (super admin only) --}}
        @can('settings.update')
            <li class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
        @endcan
    </ul>
</aside>
```

### Example 4: Inline Permission Checks

```blade
{{-- Check if user has specific permission --}}
@if(auth()->user()->can('deals.approve'))
    <button class="btn btn-success">Approve Deal</button>
@endif

{{-- Check if user has role --}}
@if(auth()->user()->hasRole('manager'))
    <div class="manager-tools">
        <!-- Manager specific tools -->
    </div>
@endif

{{-- Check multiple permissions (any) --}}
@if(auth()->user()->hasAnyPermission(['plots.create', 'properties.create']))
    <a href="#" class="btn btn-primary">Add Inventory</a>
@endif

{{-- Check multiple permissions (all) --}}
@if(auth()->user()->hasAllPermissions(['deals.create', 'deals.approve']))
    <!-- User can both create and approve deals -->
@endif

{{-- Using @unless (opposite of @can) --}}
@unless(auth()->user()->can('deals.delete'))
    <p class="text-muted">You cannot delete deals.</p>
@endunless

{{-- Using @canany --}}
@canany(['plots.update', 'properties.update'])
    <button>Edit Inventory</button>
@endcanany
```

---

## 🛣️ 8. USAGE IN ROUTES

### Web Routes with Middleware

```php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    PlotController,
    PropertyController,
    DealController,
    UserController,
    ReportController
};

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Plots - Permission-based
    Route::middleware(['permission:plots.view_all'])->group(function () {
        Route::resource('plots', PlotController::class);
    });

    // Properties - Permission-based
    Route::middleware(['permission:properties.view_all'])->group(function () {
        Route::resource('properties', PropertyController::class);
    });

    // Deals - Role-based
    Route::middleware(['role:manager|admin|dealer'])->group(function () {
        Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
        Route::post('/deals', [DealController::class, 'store'])
            ->middleware('permission:deals.create');

        // Approve - Only manager/admin
        Route::post('/deals/{deal}/approve', [DealController::class, 'approve'])
            ->middleware('role:manager|admin')
            ->name('deals.approve');
    });

    // User Management - Admin only
    Route::middleware(['role:super-admin|admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Reports - Permission-based
    Route::prefix('reports')->middleware(['permission:reports.view'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/sales', [ReportController::class, 'sales'])
            ->middleware('permission:reports.sales');
        Route::get('/financial', [ReportController::class, 'financial'])
            ->middleware('permission:reports.financial');
    });
});
```

### API Routes with Middleware

```php
// routes/api.php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // Plots API
    Route::prefix('plots')->group(function () {
        Route::get('/', [PlotController::class, 'index'])
            ->middleware('permission:plots.view_all');

        Route::post('/', [PlotController::class, 'store'])
            ->middleware('permission:plots.create');

        Route::put('/{plot}', [PlotController::class, 'update'])
            ->middleware('permission:plots.update');

        Route::delete('/{plot}', [PlotController::class, 'destroy'])
            ->middleware('permission:plots.delete');
    });

    // Deals API - Role or Permission
    Route::prefix('deals')->middleware(['role_or_permission:manager|deals.view'])->group(function () {
        Route::get('/', [DealController::class, 'index']);
        Route::post('/', [DealController::class, 'store']);

        Route::post('/{deal}/approve', [DealController::class, 'approve'])
            ->middleware('permission:deals.approve');
    });
});
```

---

## 🚀 9. ADVANCED FEATURES

### 1. Direct Permissions to Users

```php
// Assign permission directly to user (bypassing role)
$user = User::find(1);
$user->givePermissionTo('plots.create');

// Revoke permission
$user->revokePermissionTo('plots.create');

// Check direct permission
$user->hasDirectPermission('plots.create');

// Get all direct permissions
$user->getDirectPermissions();
```

### 2. Sync Permissions

```php
// Sync permissions (remove all and add new)
$role = Role::findByName('dealer');
$role->syncPermissions(['leads.create', 'leads.update', 'clients.create']);

// Sync roles for user
$user->syncRoles(['dealer', 'accountant']);
```

### 3. Wildcard Permissions

```php
// Create wildcard permission
Permission::create(['name' => 'plots.*']);

// Give all plot permissions
$role->givePermissionTo('plots.*');

// Check
$user->hasPermissionTo('plots.create'); // true if has plots.*
```

### 4. Multiple Guards

```php
// Create permission for different guard
Permission::create([
    'name' => 'edit_articles',
    'guard_name' => 'api'
]);

// Assign to role with guard
$role->givePermissionTo('edit_articles'); // web guard (default)

// Check with guard
$user->hasPermissionTo('edit_articles', 'api');
```

### 5. Blade Directives Summary

```blade
@role('admin')                      {{-- Has role --}}
@hasrole('admin')                   {{-- Same as @role --}}
@hasanyrole('admin|manager')        {{-- Has any role --}}
@hasallroles('admin|manager')       {{-- Has all roles --}}

@can('plots.create')                {{-- Has permission --}}
@cannot('plots.create')             {{-- Doesn't have permission --}}
@canany(['plots.create', 'plots.update'])  {{-- Has any permission --}}

@unlessrole('admin')                {{-- Doesn't have role --}}
```

### 6. Middleware Combinations

```php
// Require both role AND permission
Route::get('/special', function () {
    //
})->middleware(['role:admin', 'permission:special.access']);

// Require role OR permission
Route::get('/dashboard', function () {
    //
})->middleware(['role_or_permission:admin|dashboard.view']);
```

---

## 📚 10. COMPLETE EXAMPLES

### Complete CRUD with Permissions

```php
// app/Http/Controllers/PropertyController.php

<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:properties.view_all')->only('index');
        $this->middleware('permission:properties.create')->only(['create', 'store']);
        $this->middleware('permission:properties.update')->only(['edit', 'update']);
        $this->middleware('permission:properties.delete')->only('destroy');
    }

    public function index()
    {
        $properties = Property::with('society', 'propertyType')->paginate(20);
        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'society_id' => 'required|exists:societies,id',
            // ... other fields
        ]);

        $property = Property::create($validated);

        return redirect()->route('properties.index')
            ->with('success', 'Property created successfully!');
    }

    public function show(Property $property)
    {
        abort_unless(auth()->user()->can('properties.view'), 403);

        return view('properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // ... other fields
        ]);

        $property->update($validated);

        return redirect()->route('properties.index')
            ->with('success', 'Property updated successfully!');
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted successfully!');
    }
}
```

### Complete Blade View

```blade
{{-- resources/views/properties/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Properties</h1>

        @can('properties.create')
            <a href="{{ route('properties.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Property
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Society</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                    <tr>
                        <td>{{ $property->id }}</td>
                        <td>{{ $property->title }}</td>
                        <td>{{ $property->propertyType->name }}</td>
                        <td>{{ $property->society->name }}</td>
                        <td>Rs. {{ number_format($property->price_sale) }}</td>
                        <td>
                            <span class="badge bg-{{ $property->status === 'available' ? 'success' : 'secondary' }}">
                                {{ ucfirst($property->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('properties.view')
                                    <a href="{{ route('properties.show', $property) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan

                                @can('properties.update')
                                    <a href="{{ route('properties.edit', $property) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan

                                @can('properties.delete')
                                    <form action="{{ route('properties.destroy', $property) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this property?');"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No properties found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $properties->links() }}
        </div>
    </div>
</div>
@endsection
```

---

## 🔧 ARTISAN COMMANDS

### Useful Commands

```bash
# Clear permission cache
php artisan permission:cache-reset

# Create permission
php artisan permission:create-permission "plots.assign"

# Create role
php artisan permission:create-role "dealer"

# Show all permissions
php artisan permission:show

# Assign permission to role
# (No built-in command, use tinker or seeder)
php artisan tinker
>>> $role = Role::findByName('dealer');
>>> $role->givePermissionTo('leads.create');
```

---

## 📊 PERMISSION MATRIX TABLE

| Permission | Super Admin | Admin | Manager | Dealer | Accountant | Staff |
|------------|:-----------:|:-----:|:-------:|:------:|:----------:|:-----:|
| **Users** |
| users.view_all | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| users.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| users.update | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| users.delete | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **Societies** |
| societies.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| societies.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| societies.update | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| **Plots** |
| plots.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| plots.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| plots.assign | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| **Leads/Clients** |
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
| reports.financial | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| reports.commission | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |

---

## ✅ SETUP CHECKLIST

- [ ] Install Spatie Permission package
- [ ] Publish configuration and migrations
- [ ] Run migrations
- [ ] Add `HasRoles` trait to User model
- [ ] Create PermissionSeeder
- [ ] Create RoleSeeder
- [ ] Create RolePermissionSeeder
- [ ] Update UserSeeder to assign roles
- [ ] Run seeders
- [ ] Register middleware aliases
- [ ] Apply middleware to routes
- [ ] Add permission checks in controllers
- [ ] Add Blade directives in views
- [ ] Test all permissions
- [ ] Clear cache

---

## 📚 RESOURCES

- **Spatie Permission Documentation**: https://spatie.be/docs/laravel-permission
- **Laravel Authorization**: https://laravel.com/docs/authorization
- **GitHub Repository**: https://github.com/spatie/laravel-permission

---

**Created**: January 28, 2026
**Package**: Spatie Laravel Permission v6.x
**Laravel Version**: 11.x
