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
            $document->toArray(),
            'Document "' . $document->title . '" was uploaded.',
            'document.created',
            [
                'document_id' => $document->id,
                'title' => $document->title,
                'uploaded_by' => $document->uploaded_by,
                'status' => $document->status,
            ]
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

        $description = 'Document "' . $document->title . '" was updated.';

        if (isset($original['status'], $changes['status'])) {
            $description = 'Document "' . $document->title . '" status changed from "' .
                $original['status'] . '" to "' . $changes['status'] . '".';
        }

        if (isset($original['approver_id'], $changes['approver_id'])) {
            $oldApprover = $original['approver_id'] ?? 'none';
            $newApprover = $changes['approver_id'] ?? 'none';

            $description = 'Document "' . $document->title . '" current approver changed from "' .
                $oldApprover . '" to "' . $newApprover . '".';
        }

        if (
            isset($original['status'], $changes['status']) &&
            isset($original['approver_id'], $changes['approver_id'])
        ) {
            $description = 'Document "' . $document->title . '" status changed from "' .
                $original['status'] . '" to "' . $changes['status'] .
                '" and current approver was updated.';
        }

        AuditService::log(
            'updated',
            'documents',
            $document->id,
            $original,
            $changes,
            $description,
            'document.updated',
            [
                'document_id' => $document->id,
                'title' => $document->title,
                'uploaded_by' => $document->uploaded_by,
                'status' => $document->status,
                'approver_id' => $document->approver_id,
            ]
        );
    }

    public function deleted(Document $document): void
    {
        AuditService::log(
            'deleted',
            'documents',
            $document->id,
            $document->toArray(),
            null,
            'Document "' . $document->title . '" was deleted.',
            'document.deleted',
            [
                'document_id' => $document->id,
                'title' => $document->title,
                'uploaded_by' => $document->uploaded_by,
            ]
        );
    }
}