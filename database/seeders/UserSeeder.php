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
                'name' => 'Jermaine Lee',
                'email' => 'tester@uphmc.com.ph',
                'password' => Hash::make('#PIKAchu0220?'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $data) {

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'email_verified_at' => $data['email_verified_at'],
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