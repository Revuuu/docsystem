<?php

namespace App\Services;

class SidebarService
{
    public static function getMenu(string $role): array
    {
        if ($role === 'admin') {
            return [
                [
                    'label' => 'Dashboard',
                    'icon' => 'house-door',
                    'section' => 'admin-dashboard',
                ],
                [
                    'label' => 'Document Workflow',
                    'icon' => 'file-earmark-check',
                    'section' => 'workflow',
                ],
                    [
                    'label' => 'User Management',
                    'icon' => 'people-fill',
                    'section' => 'user-management',
                ],
                [
                    'label' => 'Role Assignment',
                    'icon' => 'users',
                    'section' => 'users',
                ],
                [
                    'label' => 'Audit Trail',
                    'icon' => 'file-text',
                    'section' => 'audit',
                ],
            ];
        }

        return [
            [
                'label' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'section' => 'dashboard',
            ],
            [
                'label' => 'My Documents',
                'icon' => 'folder',
                'section' => 'documents',
            ],
            [
                'label' => 'My Profile',
                'icon' => 'user-circle',
                'section' => 'profile',
            ],
        ];
    }
}