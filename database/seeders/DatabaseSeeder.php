<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin user matching legacy credentials: admin / Admin123!
        User::create([
            'username' => 'admin',
            'email' => 'admin@vermexpest.com',
            'password' => bcrypt('Admin123!'),
            'first_name' => 'Paolo M.',
            'last_name' => 'Cremat',
            'phone' => '+63 900 000 0000',
            'role' => UserRole::Admin->value,
            'sector_region' => 'Davao Head Office',
            'status' => UserStatus::Active->value,
        ]);
    }
}
