<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $this->command->info('Creating Admin User...'); // This will print in your terminal
    
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@docsystem.com'],
        [
            'name' => 'Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'admin',
        ]
    );
}
}
