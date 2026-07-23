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
        ];
    }

    /**
     * Split line items into fixed Letter-size pages while preserving order.
     *
     * @param Collection<int, object> $items
     * @return array<int, Collection<int, object>>
     */
    /**
 * Split items across Letter-size PO pages.
 *
 * Regular pages can contain more rows. The last page reserves enough
 * vertical space for totals, conditions, and five signature blocks.
 *
 * @param Collection<int, object> $items
 * @return array<int, Collection<int, object>>
 */
public function paginateItems(Collection $items): array
{
    if ($items->isEmpty()) {
        return [];
    }

    /*
     * These are visual line capacities, not strictly row counts.
     *
     * Normal page:
     *     header + item table only
     *
     * Final page:
     *     header + remaining items + totals + signatures
     *
     * For PO 90029, these values should produce two logical pages.
     */
    $normalPageCapacity = 42;
    $finalPageCapacity = 30;

    $weightedItems = $items
        ->values()
        ->map(function (object $item): array {
            return [
                'item' => $item,
                'weight' => $this->itemLineWeight($item),
            ];
        });

    /*
     * Reserve items for the last page first so the final page always
     * has room for the totals and signature section.
     */
    $finalPageItems = collect();
    $finalPageUsedLines = 0;

    for ($index = $weightedItems->count() - 1; $index >= 0; $index--) {
        $entry = $weightedItems[$index];

        if (
            $finalPageItems->isNotEmpty()
            && ($finalPageUsedLines + $entry['weight'])
                > $finalPageCapacity
        ) {
            break;
        }

        $finalPageItems->prepend($entry['item']);
        $finalPageUsedLines += $entry['weight'];
    }

    $remainingCount = $items->count() - $finalPageItems->count();

    if ($remainingCount <= 0) {
        return [
            $items->values(),
        ];
    }

    $remainingItems = $items
        ->take($remainingCount)
        ->values();

    $pages = [];
    $currentPage = collect();
    $usedLines = 0;

    foreach ($remainingItems as $item) {
        $weight = $this->itemLineWeight($item);

        if (
            $currentPage->isNotEmpty()
            && ($usedLines + $weight) > $normalPageCapacity
        ) {
            $pages[] = $currentPage->values();
            $currentPage = collect();
            $usedLines = 0;
        }

        $currentPage->push($item);
        $usedLines += $weight;
    }

    if ($currentPage->isNotEmpty()) {
        $pages[] = $currentPage->values();
    }

    $pages[] = $finalPageItems->values();

    return $pages;
}

/**
 * Estimate the rendered line count of one item row.
 */
private function itemLineWeight(object $item): int
{
    $description = trim((string) ($item->itemdesc ?? ''));

    $length = function_exists('mb_strlen')
        ? mb_strlen($description)
        : strlen($description);

    /*
     * The description column is approximately 30% of the page.
     * Around 45 characters fit on one rendered line.
     */
    return max(
        1,
        min(
            3,
            (int) ceil(max(1, $length) / 45)
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