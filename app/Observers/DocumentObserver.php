<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\AuditService;

class DocumentObserver
{
    public function created(Document $document): void
    {
        AuditService::log(
            'created',
            'documents',
            $document->id,
            null,
            $document->toArray()
        );
    }

    public function updated(Document $document): void
{
    $changes = $document->getChanges();

    unset($changes['updated_at']);

    if (empty($changes)) {
        return;
    }

    $original = array_intersect_key(
        $document->getOriginal(),
        $changes
    );

    AuditService::log(
        'updated',
        'documents',
        $document->id,
        $original,
        $changes
    );
}

    public function deleted(Document $document): void
    {
        AuditService::log(
            'deleted',
            'documents',
            $document->id,

            $document->toArray(),

            null
        );
    }
}