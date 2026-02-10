<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles
        $admin = Role::where('name', 'admin')->first();
        $manager = Role::where('name', 'manager')->first();
        $dealer = Role::where('name', 'dealer')->first();
        $accountant = Role::where('name', 'accountant')->first();
        $staff = Role::where('name', 'staff')->first();

        // Admin gets all permissions
        $allPermissions = Permission::all();
        $admin->syncPermissions($allPermissions);

        // Manager permissions (almost all except user/role management)
        $managerPermissions = Permission::where('name', 'not like', 'users.delete')
            ->where('name', '!=', 'roles.manage')
            ->where('name', '!=', 'settings.manage')
            ->get();
        $manager->syncPermissions($managerPermissions);

        // Dealer/Agent permissions
        $dealerPermissions = Permission::whereIn('name', [
            // Leads
            'leads.view', 'leads.create', 'leads.edit', 'leads.convert',
            // Clients
            'clients.view', 'clients.create', 'clients.edit',
            // Deals
            'deals.view', 'deals.create', 'deals.edit',
            // Follow-ups
            'followups.view', 'followups.create', 'followups.edit',
            // Properties & Plots (view only)
            'properties.view', 'plots.view',
            'societies.view', 'blocks.view',
            // Files (view)
            'files.view',
        ])->get();
        $dealer->syncPermissions($dealerPermissions);

        // Accountant permissions
        $accountantPermissions = Permission::whereIn('name', [
            // Payments
            'payments.view', 'payments.create', 'payments.edit', 'payments.view_all',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.view_all',
            // Files
            'files.view', 'files.edit',
            // Clients (view)
            'clients.view', 'clients.view_all',
            // Reports
            'reports.view', 'reports.export', 'reports.financial',
            // Deals (view)
            'deals.view', 'deals.view_all',
        ])->get();
        $accountant->syncPermissions($accountantPermissions);

        // Staff permissions (basic view permissions)
        $staffPermissions = Permission::whereIn('name', [
            'societies.view', 'blocks.view',
            'plots.view', 'properties.view',
            'clients.view', 'leads.view',
        ])->get();
        $staff->syncPermissions($staffPermissions);
    }
}
