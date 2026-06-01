<?php

namespace App\Observers;

use App\Models\Approval;
use App\Services\AuditService;

class ApprovalObserver
{
    public function created(Approval $approval): void
    {
        $approval->loadMissing(['document', 'user']);

        $approverName = $approval->user?->name ?? 'User ID ' . $approval->user_id;
        $documentTitle = $approval->document?->title ?? 'Document ID ' . $approval->document_id;

        AuditService::log(
            'created',
            'approvals',
            $approval->id,
            null,
            $approval->toArray(),
            'Approval step #' . $approval->step_order . ' was assigned to ' .
                $approverName . ' for document "' . $documentTitle . '".',
            'approval.created',
            [
                'approval_id' => $approval->id,
                'document_id' => $approval->document_id,
                'document_title' => $documentTitle,
                'approver_id' => $approval->user_id,
                'approver_name' => $approverName,
                'status' => $approval->status,
                'step_order' => $approval->step_order,
            ]
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

        $approval->loadMissing(['document', 'user']);

        $approverName = $approval->user?->name ?? 'User ID ' . $approval->user_id;
        $documentTitle = $approval->document?->title ?? 'Document ID ' . $approval->document_id;

        $description = $approverName . ' updated approval step #' .
            $approval->step_order . ' for document "' . $documentTitle . '".';

        if (isset($original['status'], $changes['status'])) {
            if ($changes['status'] === 'approved') {
                $description = $approverName . ' approved document "' . $documentTitle . '".';
            } elseif ($changes['status'] === 'rejected') {
                $description = $approverName . ' rejected document "' . $documentTitle . '".';
            } elseif ($changes['status'] === 'pending') {
                $description = $approverName . ' is now the current approver for document "' . $documentTitle . '".';
            } elseif ($changes['status'] === 'cancelled') {
                $description = 'Approval step for ' . $approverName . ' was cancelled for document "' . $documentTitle . '".';
            } else {
                $description = $approverName . ' approval status for document "' . $documentTitle .
                    '" changed from "' . $original['status'] . '" to "' . $changes['status'] . '".';
            }
        }

        if (isset($changes['remarks']) && $changes['remarks']) {
            $description .= ' Remarks: ' . $changes['remarks'];
        }

        AuditService::log(
            'updated',
            'approvals',
            $approval->id,
            $original,
            $changes,
            $description,
            'approval.updated',
            [
                'approval_id' => $approval->id,
                'document_id' => $approval->document_id,
                'document_title' => $documentTitle,
                'approver_id' => $approval->user_id,
                'approver_name' => $approverName,
                'status' => $approval->status,
                'step_order' => $approval->step_order,
                'remarks' => $approval->remarks,
                'duration_seconds' => $approval->duration_seconds,
            ]
        );
    }

    public function deleted(Approval $approval): void
    {
        $approval->loadMissing(['document', 'user']);

        $approverName = $approval->user?->name ?? 'User ID ' . $approval->user_id;
        $documentTitle = $approval->document?->title ?? 'Document ID ' . $approval->document_id;

        AuditService::log(
            'deleted',
            'approvals',
            $approval->id,
            $approval->toArray(),
            null,
            'Approval step #' . $approval->step_order . ' for ' .
                $approverName . ' was deleted from document "' . $documentTitle . '".',
            'approval.deleted',
            [
                'approval_id' => $approval->id,
                'document_id' => $approval->document_id,
                'document_title' => $documentTitle,
                'approver_id' => $approval->user_id,
                'approver_name' => $approverName,
                'step_order' => $approval->step_order,
            ]
        );
    }
}