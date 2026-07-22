<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentApprovalWorkflow extends Model
{
    protected $fillable = [
        'template_key',
        'name',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ordered approver assignments for this document type.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(
            DocumentApprovalWorkflowStep::class,
            'document_approval_workflow_id'
        )->orderBy('step_order');
    }

    /**
     * Administrator who originally created the workflow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Administrator who most recently updated the workflow.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /**
     * Scope for workflows that may be used for automatic extraction.
     */
    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }
}