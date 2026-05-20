<?php

namespace App\Observers;

use App\Models\Approval;
use App\Services\AuditService;

class ApprovalObserver
{
    public function created(Approval $approval): void
    {
        AuditService::log(
            'created',
            'approvals',
            $approval->id,
            null,
            $approval->toArray()
        );
    }

    public function updated(Approval $approval): void
{
    $changes = $approval->getChanges();

    unset($changes['updated_at']);

    if (empty($changes)) {
        return;
    }

    $original = array_intersect_key(
        $approval->getOriginal(),
        $changes
    );

    AuditService::log(
        'updated',
        'approvals',
        $approval->id,
        $original,
        $changes
    );
}

    public function deleted(Approval $approval): void
    {
        AuditService::log(
            'deleted',
            'approvals',
            $approval->id,

            $approval->toArray(),

            null
        );
    }
}