<?php

namespace App\Services;

class SidebarService
{
    public static function getMenu(string $role): array
    {
        if ($role === 'admin') {
            return [
                [
                    'label' => 'Signed Documents',
                    'icon' => '✅',
                    'section' => 'signed',
                ],
                [
                    'label' => 'Role Assignment',
                    'icon' => '👥',
                    'section' => 'users',
                ],
                [
                    'label' => 'Audit Trail',
                    'icon' => '📜',
                    'section' => 'audit',
                ],
                [
                    'label' => 'My Signature',
                    'icon' => '✍️',
                    'section' => 'signature',
                ],
            ];
        }

        return [
            [
                'label' => 'Dashboard',
                'section' => 'dashboard',
            ],
            [
                'label' => 'My Documents',
                'section' => 'documents',
            ],
            [
                'label' => 'My Profile',
                'section' => 'profile',
            ],
        ];
    }
}