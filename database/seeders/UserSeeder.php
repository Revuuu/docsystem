<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Staff User',
                'email' => 'staff@docsystem.com',
                'role' => 'staff',
            ],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@docsystem.com',
                'role' => 'supervisor',
            ],
            [
                'name' => 'Department Head User',
                'email' => 'depthead@docsystem.com',
                'role' => 'depthead',
            ],
            [
                'name' => 'Division User',
                'email' => 'division@docsystem.com',
                'role' => 'division',
            ],
            [
                'name' => 'Executive User',
                'email' => 'executive@docsystem.com',
                'role' => 'executive',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@docsystem.com',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $data) {

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'role' => $data['role'],
                ]
            );

            // Assign Laratrust role
            $role = Role::where('name', $data['role'])->first();

            if ($role && ! $user->hasRole($role->name)) {
                $user->addRole($role);
            }
        }

        $this->command->info('Users seeded successfully.');
    }
}