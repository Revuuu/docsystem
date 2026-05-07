<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@docsystem.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Approver
        User::updateOrCreate(
            ['email' => 'approver@docsystem.com'],
            [
                'name' => 'Approver',
                'password' => Hash::make('password123'),
                'role' => 'approver',
            ]
        );

        // Requestor
        User::updateOrCreate(
            ['email' => 'requestor@docsystem.com'],
            [
                'name' => 'Requestor',
                'password' => Hash::make('password123'),
                'role' => 'requestor',
            ]
        );

        $this->command->info('Users seeded successfully.');
    }
}