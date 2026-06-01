<?php

namespace App\Observers;

use App\Models\DocumentFile;
use App\Services\AuditService;

class DocumentFileObserver
{
    public function created(DocumentFile $file): void
    {
        $file->loadMissing(['document', 'uploader']);

        $documentTitle = $file->document?->title
            ?? 'Document ID ' . $file->document_id;

        $uploaderName = $file->uploader?->name
            ?? 'User ID ' . $file->uploaded_by;

        $fileType = $file->is_signed
            ? 'signed PDF version'
            : 'document file';

        AuditService::log(
            'created',
            'document_files',
            $file->id,
            null,
            $this->safeFileData($file),
            $uploaderName . ' created a new ' . $fileType . ' for document "' . $documentTitle . '".',
            'document_file.created',
            [
                'document_file_id' => $file->id,
                'document_id' => $file->document_id,
                'document_title' => $documentTitle,
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'version' => $file->version,
                'is_signed' => (bool) $file->is_signed,
                'is_current' => (bool) $file->is_current,
                'uploaded_by' => $file->uploaded_by,
                'uploader_name' => $uploaderName,
            ]
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

        $file->loadMissing(['document', 'uploader']);

        $documentTitle = $file->document?->title
            ?? 'Document ID ' . $file->document_id;

        $uploaderName = $file->uploader?->name
            ?? 'User ID ' . $file->uploaded_by;

        $description = 'Document file version ' . $file->version .
            ' for document "' . $documentTitle . '" was updated.';

        if (isset($original['is_current'], $changes['is_current'])) {
            if ((bool) $changes['is_current'] === true) {
                $description = 'Document file version ' . $file->version .
                    ' for document "' . $documentTitle . '" was set as the current file.';
            } else {
                $description = 'Document file version ' . $file->version .
                    ' for document "' . $documentTitle . '" is no longer the current file.';
            }
        }

        if (isset($original['is_signed'], $changes['is_signed'])) {
            $description = 'Document file version ' . $file->version .
                ' for document "' . $documentTitle . '" signed status changed.';
        }

        AuditService::log(
            'updated',
            'document_files',
            $file->id,
            $original,
            $changes,
            $description,
            'document_file.updated',
            [
                'document_file_id' => $file->id,
                'document_id' => $file->document_id,
                'document_title' => $documentTitle,
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'version' => $file->version,
                'is_signed' => (bool) $file->is_signed,
                'is_current' => (bool) $file->is_current,
                'uploaded_by' => $file->uploaded_by,
                'uploader_name' => $uploaderName,
                'changed_fields' => array_keys($changes),
            ]
        );
    }

    public function deleted(DocumentFile $file): void
    {
        $file->loadMissing(['document', 'uploader']);

        $documentTitle = $file->document?->title
            ?? 'Document ID ' . $file->document_id;

        $uploaderName = $file->uploader?->name
            ?? 'User ID ' . $file->uploaded_by;

        AuditService::log(
            'deleted',
            'document_files',
            $file->id,
            $this->safeFileData($file),
            null,
            $uploaderName . ' deleted document file version ' . $file->version .
                ' for document "' . $documentTitle . '".',
            'document_file.deleted',
            [
                'document_file_id' => $file->id,
                'document_id' => $file->document_id,
                'document_title' => $documentTitle,
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'version' => $file->version,
                'is_signed' => (bool) $file->is_signed,
                'is_current' => (bool) $file->is_current,
                'uploaded_by' => $file->uploaded_by,
                'uploader_name' => $uploaderName,
            ]
        );
    }

    private function safeFileData(DocumentFile $file): array
    {
        return [
            'id' => $file->id,
            'document_id' => $file->document_id,
            'parent_file_id' => $file->parent_file_id,
            'file_name' => $file->file_name,
            'file_path' => $file->file_path,
            'mime_type' => $file->mime_type,
            'file_size' => $file->file_size,
            'version' => $file->version,
            'is_signed' => (bool) $file->is_signed,
            'is_current' => (bool) $file->is_current,
            'uploaded_by' => $file->uploaded_by,
            'created_at' => $file->created_at,
            'updated_at' => $file->updated_at,
        ];
    }
}