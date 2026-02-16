<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password123'),
            'tenant_id' => null, // global user
        ]);

        // Attach Super Admin role
        $superAdmin->roles()->attach(
            Role::where('slug', 'super-admin')->first()
        );
    }
}
