<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ApprovalPendingNotification;
use App\Models\Approval;
use App\Models\Document;
use App\Services\DefaultApprovalWorkflowResolver;
use App\Services\Pdf\ChromePdfRenderer;
use App\Services\PurchaseOrders\PurchaseOrderDocumentService;
use App\Services\PurchaseOrders\PurchaseOrderSignatureBlockService;
use App\Services\PurchaseOrders\PurchaseOrderSignatureLayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TemporaryDocumentExtractionController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderDocumentService $purchaseOrders,
        private readonly DefaultApprovalWorkflowResolver $workflowResolver,
        private readonly PurchaseOrderSignatureLayout $signatureLayout,
        private readonly PurchaseOrderSignatureBlockService $signatureBlocks,
        private readonly ChromePdfRenderer $pdfRenderer,
    ) {
    }

    public function store(
        Request $request,
        int $poNo
    ): JsonResponse|RedirectResponse {
        $temporaryPdfPath = null;
        $storedPath = null;
        $documentTitle = "Purchase Order {$poNo}";

        $existingDocument = Document::query()
            ->where('title', $documentTitle)
            ->first();

        if ($existingDocument) {
            return $this->failureResponse(
                request: $request,
                message: "Purchase Order {$poNo} has already been forwarded for approval.",
                status: 409,
                documentId: $existingDocument->id
            );
        }

        try {
            $viewData = $this->purchaseOrders->build($poNo);

            $workflow = $this->workflowResolver->resolve(
                PurchaseOrderSignatureLayout::TEMPLATE_KEY
            );

            $workflow->loadMissing('steps.user');

            $steps = $workflow->steps
                ->sortBy('step_order')
                ->values();

            $this->signatureLayout->assertApprovalCount(
                $steps->count()
            );

            foreach ($steps as $step) {
                if (!$step->user) {
                    throw ValidationException::withMessages([
                        'approval_workflow' => sprintf(
                            'Approval order %d has no assigned user.',
                            (int) $step->step_order
                        ),
                    ]);
                }
            }

            $signatureSlots = $steps
                ->map(static function ($step): array {
                    $role = strtolower(trim((string) (
                        $step->required_role
                        ?? $step->role
                        ?? $step->user->role
                        ?? 'approver'
                    )));

                    $title = match ($role) {
                        'depthead' => 'Department Head',
                        'division' => 'Division Head',
                        'executive' => 'Executive Approver',
                        'admin' => 'Administrator',
                        default => Str::headline($role),
                    };

                    return [
                        'sequence' => (int) $step->step_order,
                        'name' => trim((string) $step->user->name),
                        'title' => $title,
                    ];
                })
                ->all();

            $html = view('purchase-orders.index', [
                ...$viewData,
                'signatureSlots' => $signatureSlots,
                'serverPdf' => true,
            ])->render();

            $temporaryPdfPath = $this->pdfRenderer->render(
                $html,
                "purchase-order-{$poNo}.pdf"
            );

            $templateHash = hash_file(
                'sha256',
                $temporaryPdfPath
            );

            $finalPageNumber = count($viewData['pages']);

            $result = DB::transaction(function () use (
                $poNo,
                $documentTitle,
                $steps,
                $temporaryPdfPath,
                $templateHash,
                $finalPageNumber,
                &$storedPath
            ): array {
                if (
                    Document::query()
                        ->where('title', $documentTitle)
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'purchase_order' =>
                            "Purchase Order {$poNo} has already been forwarded for approval.",
                    ]);
                }

                $document = Document::query()->create([
                    'title' => $documentTitle,
                    'uploaded_by' => auth()->id(),
                    'status' => 'pending',
                ]);

                $storedPath = sprintf(
                    'document/document_%d_v1.pdf',
                    $document->id
                );

                $stream = fopen($temporaryPdfPath, 'rb');

                if ($stream === false) {
                    throw ValidationException::withMessages([
                        'pdf_generation' =>
                            'The generated Purchase Order PDF could not be opened for storage.',
                    ]);
                }

                try {
                    $stored = Storage::disk('local')->put(
                        $storedPath,
                        $stream
                    );
                } finally {
                    fclose($stream);
                }

                if (!$stored) {
                    throw ValidationException::withMessages([
                        'pdf_generation' =>
                            'The generated Purchase Order PDF could not be stored.',
                    ]);
                }

                $documentFile = $document->files()->create([
                    'parent_file_id' => null,
                    'file_name' => "purchase-order-{$poNo}.pdf",
                    'file_path' => $storedPath,
                    'mime_type' => 'application/pdf',
                    'file_size' => Storage::disk('local')->size(
                        $storedPath
                    ),
                    'version' => 1,
                    'is_signed' => false,
                    'is_current' => true,
                    'uploaded_by' => auth()->id(),
                    'template_key' =>
                        PurchaseOrderSignatureLayout::TEMPLATE_KEY,
                    'template_hash' => $templateHash,
                ]);

                $approvals = collect();

                foreach ($steps as $index => $step) {
                    $isFirst = $index === 0;

                    $approvals->push(
                        Approval::query()->create([
                            'document_id' => $document->id,
                            'user_id' => $step->user_id,
                            'status' => $isFirst
                                ? 'pending'
                                : 'waiting',
                            'step_order' => (int) $step->step_order,
                            'received_at' => $isFirst
                                ? now()
                                : null,
                        ])
                    );
                }

                $this->signatureBlocks->create(
                    document: $document,
                    documentFile: $documentFile,
                    approvals: $approvals,
                    finalPageNumber: $finalPageNumber
                );

                return [
                    'document' => $document,
                    'first_approval' => $approvals->first(),
                ];
            });

            if (
                is_string($temporaryPdfPath)
                && is_file($temporaryPdfPath)
            ) {
                @unlink($temporaryPdfPath);
                $temporaryPdfPath = null;
            }

            $firstApproval = $result['first_approval'];

            if ($firstApproval instanceof Approval) {
                $firstApproval->loadMissing('user');

                if ($firstApproval->user?->email) {
                    try {
                        Mail::to($firstApproval->user->email)
                            ->queue(
                                new ApprovalPendingNotification(
                                    $firstApproval
                                )
                            );
                    } catch (Throwable $mailException) {
                        Log::warning(
                            'Purchase Order created, but the first approval notification could not be queued.',
                            [
                                'po_number' => $poNo,
                                'document_id' => $result['document']->id,
                                'approval_id' => $firstApproval->id,
                                'message' => $mailException->getMessage(),
                            ]
                        );
                    }
                }
            }

            $message =
                "Purchase Order {$poNo} was forwarded to its configured approvers.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'document_id' => $result['document']->id,
                ]);
            }

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->with('document_success', $message);
        } catch (ValidationException $exception) {
            $this->deleteStoredFile($storedPath);

            $message = collect($exception->errors())
                ->flatten()
                ->first()
                ?? 'The Purchase Order could not be generated.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $exception->errors(),
                ], 422);
            }

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->withErrors($exception->errors())
                ->with('document_error', $message);
        } catch (Throwable $exception) {
            $this->deleteStoredFile($storedPath);

            Log::error('Purchase Order forwarding failed.', [
                'po_number' => $poNo,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $message =
                'The Purchase Order could not be generated. Check the application log for details.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->with('document_error', $message);
        } finally {
            if (
                is_string($temporaryPdfPath)
                && is_file($temporaryPdfPath)
            ) {
                @unlink($temporaryPdfPath);
            }
        }
    }

    private function failureResponse(
        Request $request,
        string $message,
        int $status,
        ?int $documentId = null
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'document_id' => $documentId,
            ], $status);
        }

        return redirect()
            ->route('dashboard', ['section' => 'workflow'])
            ->with('document_error', $message);
    }

    private function deleteStoredFile(?string $storedPath): void
    {
        if (
            is_string($storedPath)
            && Storage::disk('local')->exists($storedPath)
        ) {
            Storage::disk('local')->delete($storedPath);
        }
    }
}