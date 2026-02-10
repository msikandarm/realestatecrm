<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $dealerRole = Role::where('name', 'dealer')->first();
        $accountantRole = Role::where('name', 'accountant')->first();

        // Create default admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@realestatecrm.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@realestatecrm.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '+92-300-0000000',
            ]
        );
        $admin->assignRole($adminRole);

        // Create manager user
        $manager = User::updateOrCreate(
            ['email' => 'manager@realestatecrm.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@realestatecrm.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '+92-300-1111111',
            ]
        );
        $manager->assignRole($managerRole);

        // Create dealer user
        $dealer = User::updateOrCreate(
            ['email' => 'dealer@realestatecrm.com'],
            [
                'name' => 'Dealer User',
                'email' => 'dealer@realestatecrm.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '+92-300-2222222',
            ]
        );
        $dealer->assignRole($dealerRole);

        // Create accountant user
        $accountant = User::updateOrCreate(
            ['email' => 'accountant@realestatecrm.com'],
            [
                'name' => 'Accountant User',
                'email' => 'accountant@realestatecrm.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '+92-300-3333333',
            ]
        );
        $accountant->assignRole($accountantRole);
    }
}
