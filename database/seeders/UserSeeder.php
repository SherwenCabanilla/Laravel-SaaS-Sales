<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $payoutAdminRole = Role::where('slug', 'payout-admin')->first();

       
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'], 
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'tenant_id' => null, // global user
                'role' => 'super_admin',
            ]
        );

        // 3. Attach the role (pivot table)
        // Check if not already attached to avoid duplication
        if (!$superAdmin->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdmin->roles()->attach($superAdminRole);
        }

        $payoutAdmin = User::updateOrCreate(
            ['email' => 'platform.finance.admin@gmail.com'],
            [
                'name' => 'Platform Finance Admin',
                'password' => Hash::make('PayoutAdmin#2026'),
                'tenant_id' => null,
                'role' => 'payout_admin',
                'status' => 'active',
                'activation_state' => 'active',
                'must_change_password' => false,
            ]
        );

        if ($payoutAdminRole && ! $payoutAdmin->roles()->where('role_id', $payoutAdminRole->id)->exists()) {
            $payoutAdmin->roles()->attach($payoutAdminRole);
        }
    }
}
