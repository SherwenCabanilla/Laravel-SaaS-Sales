<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Account Owner', 'slug' => 'account-owner'],
            ['name' => 'Marketing Manager', 'slug' => 'marketing-manager'],
            ['name' => 'Sales Agent', 'slug' => 'sales-agent'],
            ['name' => 'Finance', 'slug' => 'finance'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']], // checks if slug exists
                ['name' => $role['name']]
            );
        }
    }
}
