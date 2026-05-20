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

    AuditService::log(
        'created',
        'users',
        $user->id,
        null,
        $data
    );
}
    public function updated(User $user): void
{
    $changes = $user->getChanges();

    unset($changes['updated_at']);
    unset($changes['remember_token']);
    unset($changes['password']);

    if (empty($changes)) {
        return;
    }

    $original = array_intersect_key(
        $user->getOriginal(),
        $changes
    );

    unset($original['password']);

    AuditService::log(
        'updated',
        'users',
        $user->id,
        $original,
        $changes
    );
}
public function deleted(User $user): void
{
    $data = $user->toArray();

    unset($data['password']);

    AuditService::log(
        'deleted',
        'users',
        $user->id,
        $data,
        null
    );
}
}