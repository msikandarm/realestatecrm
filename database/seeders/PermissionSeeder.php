<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Society Management
            'societies.view', 'societies.create', 'societies.edit', 'societies.delete',

            // Block Management
            'blocks.view', 'blocks.create', 'blocks.edit', 'blocks.delete',

            // Street Management
            'streets.view', 'streets.create', 'streets.edit', 'streets.delete',

            // Plot Management
            'plots.view', 'plots.create', 'plots.edit', 'plots.delete', 'plots.assign',

            // Property Management
            'properties.view', 'properties.create', 'properties.edit', 'properties.delete',

            // Client Management
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete', 'clients.view_all',

            // Lead Management
            'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.convert', 'leads.view_all',

            // Deal Management
            'deals.view', 'deals.create', 'deals.edit', 'deals.delete', 'deals.approve', 'deals.view_all',

            // File Management
            'files.view', 'files.create', 'files.edit', 'files.delete', 'files.transfer',

            // Payment Management
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete', 'payments.view_all', 'payments.approve',

            // Expense Management
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.view_all', 'expenses.approve',

            // User Management
            'users.view', 'users.create', 'users.edit', 'users.delete', 'roles.manage',

            // Dealer Management
            'dealers.view', 'dealers.create', 'dealers.edit', 'dealers.delete',

            // Report Management
            'reports.view', 'reports.export', 'reports.financial',

            // City Management
            'cities.view', 'cities.create', 'cities.edit', 'cities.delete',

            // Follow-up Management
            'followups.view', 'followups.create', 'followups.edit', 'followups.delete',

            // Settings
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
