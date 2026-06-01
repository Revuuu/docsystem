<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;

class UserObserver
{
    public function created(User $user): void
    {
        $data = $user->toArray();

        unset($data['password']);
        unset($data['remember_token']);

        AuditService::log(
            'created',
            'users',
            $user->id,
            null,
            $data,
            'User account "' . $user->name . '" was created.',
            'user.created',
            [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_email' => $user->email,
                'role' => $user->role,
            ]
        );
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();

        unset($changes['updated_at']);
        unset($changes['remember_token']);

        $passwordChanged = array_key_exists('password', $changes);

        unset($changes['password']);

        if (empty($changes) && !$passwordChanged) {
            return;
        }

        $original = array_intersect_key(
            $user->getOriginal(),
            $changes
        );

        unset($original['password']);
        unset($original['remember_token']);

        $description = 'User account "' . $user->name . '" was updated.';
        $event = 'user.updated';

        if ($passwordChanged && empty($changes)) {
            $description = 'User account "' . $user->name . '" changed password.';
            $event = 'user.password_changed';
        }

        if (isset($original['name'], $changes['name'])) {
            $description = 'User changed name from "' .
                $original['name'] . '" to "' . $changes['name'] . '".';
            $event = 'user.profile_updated';
        }

        if (isset($original['email'], $changes['email'])) {
            $description = 'User "' . $user->name . '" changed email from "' .
                $original['email'] . '" to "' . $changes['email'] . '".';
            $event = 'user.profile_updated';
        }

        if (isset($original['role'], $changes['role'])) {
            $description = 'User "' . $user->name . '" role changed from "' .
                $original['role'] . '" to "' . $changes['role'] . '".';
            $event = 'user.role_changed';
        }

        if (isset($original['signature_path'], $changes['signature_path'])) {
            if ($changes['signature_path'] === null) {
                $description = 'User "' . $user->name . '" removed signature.';
                $event = 'signature.removed';
            } elseif (empty($original['signature_path'])) {
                $description = 'User "' . $user->name . '" added signature.';
                $event = 'signature.added';
            } else {
                $description = 'User "' . $user->name . '" updated signature.';
                $event = 'signature.updated';
            }
        }

        if ($passwordChanged && !empty($changes)) {
            $description .= ' Password was also changed.';
        }

        AuditService::log(
            'updated',
            'users',
            $user->id,
            $original,
            $changes,
            $description,
            $event,
            [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_email' => $user->email,
                'role' => $user->role,
                'password_changed' => $passwordChanged,
                'changed_fields' => array_keys($changes),
            ]
        );
    }

    public function deleted(User $user): void
    {
        $data = $user->toArray();

        unset($data['password']);
        unset($data['remember_token']);

        AuditService::log(
            'deleted',
            'users',
            $user->id,
            $data,
            null,
            'User account "' . $user->name . '" was deleted.',
            'user.deleted',
            [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_email' => $user->email,
                'role' => $user->role,
            ]
        );
    }
}