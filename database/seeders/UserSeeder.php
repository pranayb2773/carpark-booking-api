<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@carpark.com',
            'password' => bcrypt('password'),
            'date_of_birth' => '1990-01-01',
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // create default customer user
        DB::table('users')->insert([
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('customer123'),
            'date_of_birth' => '2000-01-01',
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create additional random customer users.
        User::factory(10)->customer()->create();
    }
}
