<?php

namespace App\Services\PurchaseOrders;

use Illuminate\Validation\ValidationException;

class PurchaseOrderSignatureLayout
{
    public const TEMPLATE_KEY = 'purchase_order';

    public const SLOT_COUNT = 5;

    /**
     * Fixed signature rectangles in normalized PDF coordinates.
     *
     * The page number is resolved at generation time because the item table
     * may create multiple pages. Every signature belongs on the final page.
     *
     * @return array<int, array{
     *     sequence:int,
     *     slot_key:string,
     *     label:string,
     *     page_number:int,
     *     x:float,
     *     y:float,
     *     width:float,
     *     height:float
     * }>
     */
    public function slots(int $finalPageNumber): array
    {
        if ($finalPageNumber < 1) {
            throw new \InvalidArgumentException(
                'The final Purchase Order page number must be at least 1.'
            );
        }

        $definitions = [
            [
                'sequence' => 1,
                'slot_key' => 'po_signature_1',
                'label' => 'Endorsed by 1',
                'x' => 0.055,
                'y' => 0.774,
                'width' => 0.245,
                'height' => 0.050,
            ],
            [
                'sequence' => 2,
                'slot_key' => 'po_signature_2',
                'label' => 'Endorsed by 2',
                'x' => 0.315,
                'y' => 0.774,
                'width' => 0.245,
                'height' => 0.050,
            ],
            [
                'sequence' => 3,
                'slot_key' => 'po_signature_3',
                'label' => 'Endorsed by 3',
                'x' => 0.575,
                'y' => 0.774,
                'width' => 0.365,
                'height' => 0.050,
            ],
            [
                'sequence' => 4,
                'slot_key' => 'po_signature_4',
                'label' => 'Administration approval',
                'x' => 0.675,
                'y' => 0.842,
                'width' => 0.245,
                'height' => 0.050,
            ],
            [
                'sequence' => 5,
                'slot_key' => 'po_signature_5',
                'label' => 'Final approval',
                'x' => 0.675,
                'y' => 0.913,
                'width' => 0.245,
                'height' => 0.050,
            ],
        ];

        return array_map(
            static fn (array $definition): array => [
                ...$definition,
                'page_number' => $finalPageNumber,
            ],
            $definitions
        );
    }

    public function assertApprovalCount(int $approvalCount): void
    {
        if ($approvalCount === self::SLOT_COUNT) {
            return;
        }

        throw ValidationException::withMessages([
            'approval_workflow' => sprintf(
                'The Purchase Order template requires exactly %d approvers; the active workflow contains %d.',
                self::SLOT_COUNT,
                $approvalCount
            ),
        ]);
    }
}