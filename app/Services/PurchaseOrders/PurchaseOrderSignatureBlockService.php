<?php

namespace App\Services\PurchaseOrders;

use App\Models\Approval;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\DocumentSignatureBlock;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PurchaseOrderSignatureBlockService
{
    public function __construct(
        private readonly PurchaseOrderSignatureLayout $layout
    ) {
    }

    /**
     * Create one logical, fixed PDF signature rectangle for each approval.
     *
     * @param Collection<int, Approval> $approvals
     * @return Collection<int, DocumentSignatureBlock>
     */
    public function create(
        Document $document,
        DocumentFile $documentFile,
        Collection $approvals,
        int $finalPageNumber
    ): Collection {
        $orderedApprovals = $approvals
            ->sortBy('step_order')
            ->values();

        $this->layout->assertApprovalCount(
            $orderedApprovals->count()
        );

        $slots = collect(
            $this->layout->slots($finalPageNumber)
        )->keyBy('sequence');
        $created = collect();

        foreach ($orderedApprovals as $approval) {
            $sequence = (int) $approval->step_order;
            $slot = $slots->get($sequence);

            if (!is_array($slot)) {
                throw ValidationException::withMessages([
                    'approval_workflow' =>
                        "No Purchase Order signature slot exists for approval order {$sequence}.",
                ]);
            }

            $block = DocumentSignatureBlock::query()->firstOrNew([
                'approval_id' => $approval->id,
            ]);
            $block->forceFill([
                'document_id' => $document->id,
                'document_file_id' => $documentFile->id,
                'assigned_user_id' => $approval->user_id,
                'template_key' => PurchaseOrderSignatureLayout::TEMPLATE_KEY,
                'label' => $slot['label'],
                'field_name' => null,
                'slot_key' => $slot['slot_key'],
                'sequence' => $sequence,
                'page_number' => $slot['page_number'],
                'x' => $slot['x'],
                'y' => $slot['y'],
                'width' => $slot['width'],
                'height' => $slot['height'],
                'status' => 'available',
                'signed_by_user_id' => null,
                'signed_document_file_id' => null,
                'signed_at' => null,
            ]);
            $block->save();
            $created->push($block);
        }

        return $created;
    }
}