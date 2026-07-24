<?php

namespace App\Services\PurchaseOrders;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderDocumentService
{
    /**
     * Load and prepare one Purchase Order from its line-item rows.
     *
     * @return array<string, mixed>
     */
    public function build(int $poNo): array
    {
        $rows = $this->query()
            ->where('PONo', $poNo)
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'purchase_order' =>
                    "Purchase Order [{$poNo}] was not found in purchase_order_test.",
            ]);
        }

        return $this->prepare($rows);
    }

    /**
     * Prepare already-loaded source rows for the printable Blade template.
     *
     * @param Collection<int, object> $rows
     * @return array<string, mixed>
     */
    public function prepare(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'purchase_order' =>
                    'The Purchase Order contains no item rows.',
            ]);
        }

        $purchaseOrder = $rows->first();
        $subtotal = (float) $rows->sum(
            static fn (object $row): float =>
                (float) ($row->Amount ?? 0)
        );
        $discount = (float) ($purchaseOrder->discount ?? 0);
        $storedTotal = $purchaseOrder->totAmount ?? null;
        $totalAmount = $storedTotal !== null
            ? (float) $storedTotal
            : max(0, $subtotal - $discount);

        return [
            'purchaseOrder' => $purchaseOrder,
            'rows' => $rows,
            'pages' => $this->paginateItems($rows),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'totalAmount' => $totalAmount,
            'poDate' => $this->formatDate(
                $purchaseOrder->PODate ?? null
            ),
            'deliveryDate' => $this->formatDate(
                $purchaseOrder->deliverdate ?? null
            ),
            'purchaseRequestText' => trim(
                (string) (
                    $purchaseOrder->remarks
                    ?? $purchaseOrder->ReqNo
                    ?? ''
                )
            ),
        ];
    }

   /**
 * Split items across Purchase Order pages.
 *
 * Continuation pages are filled as much as possible.
 * The remaining items are placed on the final page,
 * which also contains the summary and signature footer.
 *
 * @param Collection<int, object> $items
 * @return array<int, Collection<int, object>>
 */
public function paginateItems(
    Collection $items
): array {
    if ($items->isEmpty()) {
        return [];
    }

    /*
     * Visual line capacities, not raw item counts.
     *
     * Continuation pages have no signature footer,
     * so they can contain more item lines.
     *
     * The final page reserves space for:
     * - Expected delivery date
     * - Nothing follows
     * - Discount and total
     * - Signature footer
     */
    $regularPageCapacity = 46;
    $finalPageCapacity = 30;

    $queue = $items
        ->values()
        ->map(function (object $item): array {
            return [
                'item' => $item,
                'weight' =>
                    $this->itemLineWeight($item),
            ];
        });

    $remainingWeight = (int) $queue->sum(
        'weight'
    );

    $pages = [];

    /*
     * Fill continuation pages from the beginning.
     *
     * Keep at least one item for the final page so the
     * footer does not appear on a separate itemless page.
     */
    while (
        $queue->count() > 1 &&
        $remainingWeight >
            $finalPageCapacity
    ) {
        $pageEntries = collect();
        $usedLines = 0;

        while ($queue->count() > 1) {
            $entry = $queue->first();

            if (!is_array($entry)) {
                $queue->shift();
                continue;
            }

            $weight = (int) $entry['weight'];

            /*
             * Stop when the next item would exceed the
             * continuation-page capacity.
             */
            if (
                $pageEntries->isNotEmpty() &&
                $usedLines + $weight >
                    $regularPageCapacity
            ) {
                break;
            }

            $queue->shift();

            $pageEntries->push($entry);

            $usedLines += $weight;
            $remainingWeight -= $weight;

            /*
             * This page is full enough. Continue the
             * remaining items on the next page.
             */
            if (
                $usedLines >=
                $regularPageCapacity
            ) {
                break;
            }

            /*
             * The remaining items now fit on the final
             * page with its summary and signature footer.
             */
            if (
                $remainingWeight <=
                $finalPageCapacity
            ) {
                break;
            }
        }

        /*
         * Safety fallback to prevent an infinite loop.
         */
        if ($pageEntries->isEmpty()) {
            $entry = $queue->shift();

            if (is_array($entry)) {
                $pageEntries->push($entry);

                $remainingWeight -=
                    (int) $entry['weight'];
            }
        }

        if ($pageEntries->isNotEmpty()) {
            $pages[] = $pageEntries
                ->pluck('item')
                ->values();
        }
    }

    /*
     * Everything left becomes the final page.
     */
    if ($queue->isNotEmpty()) {
        $pages[] = $queue
            ->pluck('item')
            ->values();
    }

    return $pages;
}

/**
 * Estimate the rendered line count of one item row.
 */
/**
 * Estimate the number of rendered text lines
 * occupied by a Purchase Order item.
 */
private function itemLineWeight(
    object $item
): int {
    $description = trim(
        (string) (
            $item->itemdesc ?? ''
        )
    );

    $length = function_exists('mb_strlen')
        ? mb_strlen($description)
        : strlen($description);

    /*
     * With the current Letter layout, 30% description
     * column and Tahoma 8pt font, approximately
     * 45 characters fit on one line.
     */
    return max(
        1,
        min(
            3,
            (int) ceil(
                max(1, $length) / 45
            )
        )
    );
}

    private function query(): Builder
    {
        return DB::table('purchase_order_test')->select([
            'id',
            'GuarantorName',
            'PONo',
            'PODate',
            'ItemId',
            'itemdesc',
            'price',
            'qty',
            'unit',
            'Amount',
            'totAmount',
            'curramt',
            'remarks',
            'PK_TRXNO',
            'fullname',
            'prcontactperson',
            'discount',
            'vatamt',
            'vatincl',
            'praddress',
            'prtelno',
            'prfaxno',
            'telefax',
            'mobilephone',
            'ReqNo',
            'conversion',
            'Terms',
            'fk_mscwarehouse',
            'deliverdate',
            'SOH',
            'PRremarks',
        ]);
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)
                ->format('m/d/Y');
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }
}