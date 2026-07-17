<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\DocumentSignatureBlock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentSignatureBlockService
{
    /**
     * Create signature blocks for approvals that currently exist.
     *
     * This does not require every configured role to already
     * have an approval record.
     */
    public function createFromExistingApprovals(
        Document $document,
        DocumentFile $documentFile,
        array $template
    ): Collection {
        $approvals = $document->approvals()
            ->with('user')
            ->orderBy('step_order')
            ->get();

        if ($approvals->isEmpty()) {
            throw ValidationException::withMessages([
                'approvals' =>
                    'The document has no approval workflow.',
            ]);
        }

        return DB::transaction(function () use (
            $approvals,
            $documentFile,
            $template
        ) {
            $createdBlocks = new Collection();

            foreach ($approvals as $approval) {
                $createdBlocks->push(
                    $this->createForApproval(
                        approval: $approval,
                        documentFile: $documentFile,
                        template: $template
                    )
                );
            }

            return $createdBlocks;
        });
    }

    /**
     * Create one fixed signature block for one approval.
     *
     * Call this whenever your system creates a new approval
     * during workflow progression.
     */
    public function createForApproval(
        Approval $approval,
        DocumentFile $documentFile,
        array $template
    ): DocumentSignatureBlock {
        $approval->loadMissing('user');

        if (!$approval->user) {
            throw ValidationException::withMessages([
                'approval' =>
                    'The approval has no assigned user.',
            ]);
        }

        $role = $this->resolveUserRole(
            $approval->user
        );

        if (!$role) {
            throw ValidationException::withMessages([
                'approval' =>
                    'The assigned user has no supported approval role.',
            ]);
        }

        $blockDefinition = collect(
            $template['blocks'] ?? []
        )->first(
            fn (array $block): bool =>
                ($block['role'] ?? null) === $role
        );

        if (!$blockDefinition) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    "No signature block is configured for role [{$role}].",
            ]);
        }

        $this->validateBlockDefinition(
            $blockDefinition
        );

        return DB::transaction(function () use (
            $approval,
            $documentFile,
            $template,
            $blockDefinition
        ) {
            $status = $this->resolveInitialStatus(
                $approval->status
            );

            return DocumentSignatureBlock::query()
                ->updateOrCreate(
                    [
                        'approval_id' => $approval->id,
                    ],
                    [
                        'document_id' =>
                            $approval->document_id,

                        'document_file_id' =>
                            $documentFile->id,

                        'assigned_user_id' =>
                            $approval->user_id,

                        'template_key' =>
                            $template['key'],

                        'label' =>
                            $blockDefinition['label'],

                        'field_name' =>
                            trim((string) $blockDefinition['field_name']),


                        /*
                         * Use the template position sequence,
                         * not the approval step_order.
                         */
                        'sequence' =>
                            $blockDefinition['sequence'],

                        'page_number' =>
                            $blockDefinition['page_number'],

                        'x' =>
                            $blockDefinition['x'],

                        'y' =>
                            $blockDefinition['y'],

                        'width' =>
                            $blockDefinition['width'],

                        'height' =>
                            $blockDefinition['height'],

                        'status' => $status,
                    ]
                );
        });
    }

    /**
     * Activate a block when its approval becomes pending.
     */
    public function activateForApproval(
        int $approvalId
    ): DocumentSignatureBlock {
        return DB::transaction(function () use (
            $approvalId
        ) {
            $block = DocumentSignatureBlock::query()
                ->where('approval_id', $approvalId)
                ->lockForUpdate()
                ->first();

            if (!$block) {
                throw ValidationException::withMessages([
                    'signature_block' =>
                        'No signature block is assigned to this approval.',
                ]);
            }

            if (
                $block->status ===
                DocumentSignatureBlock::STATUS_SIGNED
            ) {
                throw ValidationException::withMessages([
                    'signature_block' =>
                        'This signature block has already been signed.',
                ]);
            }

            if (
                $block->status ===
                DocumentSignatureBlock::STATUS_CANCELLED
            ) {
                throw ValidationException::withMessages([
                    'signature_block' =>
                        'This signature block has been cancelled.',
                ]);
            }

            $block->update([
                'status' =>
                    DocumentSignatureBlock::STATUS_AVAILABLE,
            ]);

            return $block->fresh();
        });
    }

    /**
     * Cancel unsigned blocks after rejection.
     */
    public function cancelRemaining(
        Document $document
    ): int {
        return DocumentSignatureBlock::query()
            ->where('document_id', $document->id)
            ->whereIn('status', [
                DocumentSignatureBlock::STATUS_LOCKED,
                DocumentSignatureBlock::STATUS_AVAILABLE,
            ])
            ->update([
                'status' =>
                    DocumentSignatureBlock::STATUS_CANCELLED,
            ]);
    }

    /**
     * Resolve the user's workflow role.
     */
    private function resolveUserRole(
        object $user
    ): ?string {
        /*
         * Your users table contains a direct role column.
         */
        $directRole = $user->getAttribute('role');

        if (
            is_string($directRole) &&
            trim($directRole) !== ''
        ) {
            return trim($directRole);
        }

        /*
         * Fallback for Laratrust role assignments.
         */
        if (method_exists($user, 'getRoles')) {
            $role = $user->getRoles()
                ->pluck('name')
                ->first(
                    fn ($name) =>
                        in_array(
                            $name,
                            [
                                'staff',
                                'supervisor',
                                'depthead',
                                'division',
                                'executive',
                            ],
                            true
                        )
                );

            return is_string($role)
                ? $role
                : null;
        }

        return null;
    }

    /**
     * Determine the signature-block state from the approval.
     */
    private function resolveInitialStatus(
        string $approvalStatus
    ): string {
        return match ($approvalStatus) {
            'pending' =>
                DocumentSignatureBlock::STATUS_AVAILABLE,

            'rejected' =>
                DocumentSignatureBlock::STATUS_CANCELLED,

            default =>
                DocumentSignatureBlock::STATUS_LOCKED,
        };
    }

    /**
     * Validate normalized template coordinates.
     */
    private function validateBlockDefinition(
        array $block
    ): void {
        $required = [
            'sequence',
            'role',
            'label',
            'page_number',
            'x',
            'y',
            'width',
            'height',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $block)) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Signature block field [{$field}] is missing.",
                ]);
            }
        }

        if ((int) $block['sequence'] < 1) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    'Signature block sequence must be at least 1.',
            ]);
        }

        if ((int) $block['page_number'] < 1) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    'Signature block page number must be at least 1.',
            ]);
        }

        foreach (
            ['x', 'y', 'width', 'height']
            as $coordinate
        ) {
            if (!is_numeric($block[$coordinate])) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Signature block [{$coordinate}] must be numeric.",
                ]);
            }

            $value = (float) $block[$coordinate];

            if ($value < 0 || $value > 1) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Signature block [{$coordinate}] must be between 0 and 1.",
                ]);
            }
        }

        if (
            (float) $block['width'] <= 0 ||
            (float) $block['height'] <= 0
        ) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    'Signature block width and height must be greater than zero.',
            ]);
        }

        if (
            (float) $block['x'] +
            (float) $block['width'] > 1
        ) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    'Signature block extends beyond the page width.',
            ]);
        }

        if (
            (float) $block['y'] +
            (float) $block['height'] > 1
        ) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    'Signature block extends beyond the page height.',
            ]);
        }
    }
}