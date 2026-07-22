<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'document_approval_workflow_id',
        'step_order',
        'required_role',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
        ];
    }

    /**
     * Workflow that owns this approval step.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(
            DocumentApprovalWorkflow::class,
            'document_approval_workflow_id'
        );
    }

    /**
     * Specific user assigned to this approval step.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}