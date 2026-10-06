<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'username' => 'test',
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('Test User: test / password');
        $this->command->info('Admin: admin / admin123');
    }
}
