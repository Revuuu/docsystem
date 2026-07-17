<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignatureBlock extends Model
{
    public const STATUS_LOCKED = 'locked';

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_SIGNED = 'signed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'document_id',
        'document_file_id',
        'approval_id',
        'assigned_user_id',
        'template_key',
        'label',
        'field_name',
        'sequence',
        'page_number',
        'x',
        'y',
        'width',
        'height',
        'status',
        'signed_by_user_id',
        'signed_document_file_id',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'document_id' => 'integer',
            'document_file_id' => 'integer',
            'approval_id' => 'integer',
            'assigned_user_id' => 'integer',
            'sequence' => 'integer',
            'page_number' => 'integer',

            /*
             * Cast normalized coordinates to floats so they
             * can be multiplied by the PDF page dimensions.
             */
            'x' => 'float',
            'y' => 'float',
            'width' => 'float',
            'height' => 'float',

            'signed_by_user_id' => 'integer',
            'signed_document_file_id' => 'integer',
            'signed_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function documentFile(): BelongsTo
    {
        return $this->belongsTo(
            DocumentFile::class,
            'document_file_id'
        );
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_user_id'
        );
    }

    public function signedByUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'signed_by_user_id'
        );
    }

    public function signedDocumentFile(): BelongsTo
    {
        return $this->belongsTo(
            DocumentFile::class,
            'signed_document_file_id'
        );
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}