<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseOrderQueueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('purchase_order_test')
            ->select('PONo')
            ->selectRaw('MIN("PODate") AS po_date')
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    NULLIF(MAX("fullname"), ''),
                    NULLIF(MAX("GuarantorName"), ''),
                    'Unknown supplier'
                ) AS supplier
                SQL
            )
            ->selectRaw('COUNT(*) AS item_count')
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    MAX("totAmount"),
                    SUM(COALESCE("Amount", 0)),
                    0
                ) AS total_amount
                SQL
            )
            ->whereNotNull('PONo')
            ->groupBy('PONo')
            ->orderByDesc('PONo');

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';

            $query->where(function ($builder) use ($like): void {
                $builder
                    ->whereRaw(
                        'LOWER(CAST("PONo" AS TEXT)) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE("fullname", \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE("GuarantorName", \'\')) LIKE ?',
                        [$like]
                    );
            });
        }

        /** @var LengthAwarePaginator $purchaseOrders */
        $purchaseOrders = $query->paginate(
            perPage: 10,
            page: max(1, (int) $request->query('page', 1))
        );

        $titles = collect($purchaseOrders->items())
            ->map(
                static fn (object $purchaseOrder): string =>
                    'Purchase Order ' . $purchaseOrder->PONo
            );

        $documents = Document::query()
            ->with([
                'approvals.user',
                'files',
            ])
            ->whereIn('title', $titles)
            ->get()
            ->keyBy('title');

        $rows = collect($purchaseOrders->items())
            ->map(function (object $purchaseOrder) use ($documents): array {
                $poNo = (string) $purchaseOrder->PONo;
                $document = $documents->get(
                    'Purchase Order ' . $poNo
                );

                return [
                    'po_no' => $poNo,
                    'supplier' => (string) $purchaseOrder->supplier,
                    'po_date' => $this->formatDate(
                        $purchaseOrder->po_date
                    ),
                    'item_count' => (int) $purchaseOrder->item_count,
                    'total_amount' => (float) $purchaseOrder->total_amount,
                    'workflow' => $this->workflowData($document),
                ];
            })
            ->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $purchaseOrders->currentPage(),
                'last_page' => $purchaseOrders->lastPage(),
                'per_page' => $purchaseOrders->perPage(),
                'total' => $purchaseOrders->total(),
            ],
        ]);
    }

    private function workflowData(?Document $document): array
    {
        if (!$document) {
            return [
                'forwarded' => false,
                'status' => 'Not Forwarded',
                'status_key' => 'not_forwarded',
                'progress' => 0,
                'current_signatory' => null,
                'current_signatory_role' => null,
                'view_url' => null,
                'download_url' => null,
            ];
        }

        $approvals = $document->approvals
            ->sortBy('step_order')
            ->values();

        $totalSteps = $approvals->count();
        $approvedSteps = $approvals
            ->where('status', 'approved')
            ->count();

        $progress = $totalSteps > 0
            ? (int) round(($approvedSteps / $totalSteps) * 100)
            : 0;

        $currentApproval = $approvals
            ->firstWhere('status', 'pending');

        $latestFile = $document->files
            ->firstWhere('is_current', true)
            ?? $document->files
                ->sortByDesc('version')
                ->first();

        $rawStatus = strtolower(
            (string) ($document->status ?? 'pending')
        );

        $displayStatus = match ($rawStatus) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Ongoing',
        };

        return [
            'forwarded' => true,
            'document_id' => $document->id,
            'status' => $displayStatus,
            'status_key' => $rawStatus,
            'progress' => $progress,
            'current_signatory' => $currentApproval?->user?->name,
            'current_signatory_role' => $currentApproval?->user?->role,
            'view_url' => $latestFile
                ? route('files.view', encrypt($latestFile->id))
                : null,
            'download_url' => $latestFile
                ? route('files.download', encrypt($latestFile->id))
                : null,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)
                ->format('M d, Y');
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }
}