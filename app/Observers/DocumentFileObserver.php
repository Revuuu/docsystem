<?php

namespace App\Observers;

use App\Models\DocumentFile;
use App\Services\AuditService;

class DocumentFileObserver
{
    public function created(DocumentFile $file): void
    {
        AuditService::log(
            'created',
            'document_files',
            $file->id,
            null,
            $file->toArray()
        );
    }

    public function updated(DocumentFile $file): void
    {
        $changes = $file->getChanges();

        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $original = array_intersect_key(
            $file->getOriginal(),
            $changes
        );

        AuditService::log(
            'updated',
            'document_files',
            $file->id,
            $original,
            $changes
        );
    }

    public function deleted(DocumentFile $file): void
    {
        AuditService::log(
            'deleted',
            'document_files',
            $file->id,
            $file->toArray(),
            null
        );
    }
}