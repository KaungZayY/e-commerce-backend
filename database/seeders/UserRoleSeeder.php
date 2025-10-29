<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(UserRole::count() === 0) {
            UserRole::create([
                'role_name' => 'admin'
            ]);
            UserRole::create([
                'role_name' => 'customer'
            ]);
        }
    }
}
