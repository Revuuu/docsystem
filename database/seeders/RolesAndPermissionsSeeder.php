<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CREATE ROLES
        |--------------------------------------------------------------------------
        */
         $staff = Role::create([
            'name' => 'staff',
            'display_name' => 'Staff',
        ]);

        $supervisor = Role::create([
            'name' => 'supervisor',
            'display_name' => 'Supervisor',
        ]);
        $depthead = Role::create([
            'name' => 'depthead',
            'display_name' => 'Department Head',
        ]);
        $division = Role::create([
            'name' => 'division',
            'display_name' => 'Division',
        ]);
        $executive = Role::create([
            'name' => 'executive',
            'display_name' => 'CEO',
        ]);
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);

         /*
        |--------------------------------------------------------------------------
        | CREATE PERMISSIONS
        |--------------------------------------------------------------------------
        */
         $createDocuments = Permission::create([
            'name' => 'create-documents',
            'display_name' => 'Create Documents',
        ]);

        $approveSupervisor = Permission::create([
            'name' => 'approve-supervisor',
            'display_name' => 'Approve as Supervisor',
        ]);

        $approveDepartmentHead = Permission::create([
            'name' => 'approve-department-head',
            'display_name' => 'Approve as Department Head',
        ]);

        $approveDivision = Permission::create([
            'name' => 'approve-division',
            'display_name' => 'Approve as Division',
        ]);

        $approveCeo = Permission::create([
            'name' => 'approve-ceo',
            'display_name' => 'Approve as CEO',
        ]);

        $manageUsers = Permission::create([
            'name' => 'manage-users',
            'display_name' => 'Manage Users',
        ]);

        $manageDocuments = Permission::create([
            'name' => 'manage-documents',
            'display_name' => 'Manage Documents',
        ]);

         /*
        |--------------------------------------------------------------------------
        | ASSIGN PERMISSIONS TO ROLES
        |--------------------------------------------------------------------------
        */

        $staff->givePermission($createDocuments);

        $supervisor->givePermission($approveSupervisor);

        $depthead->givePermission($approveDepartmentHead);

        $division->givePermission($approveDivision);

        $executive->givePermission($approveCeo);

        $admin->givePermissions([
            $manageUsers,
            $manageDocuments,
        ]);
    }
}
