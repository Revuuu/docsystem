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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TemporaryDocumentExtractionController extends Controller
{
    private const TEST_PO_NUMBER = 90029;

    public function __construct(
        private readonly PurchaseOrderDocumentService $purchaseOrders,
        private readonly DefaultApprovalWorkflowResolver $workflowResolver,
        private readonly PurchaseOrderSignatureLayout $signatureLayout,
        private readonly PurchaseOrderSignatureBlockService $signatureBlocks,
        private readonly ChromePdfRenderer $pdfRenderer,
    ) {
    }

    public function store(): RedirectResponse
    {
        $temporaryPdfPath = null;
        $storedPath = null;

        try {
            $viewData = $this->purchaseOrders->build(
                self::TEST_PO_NUMBER
            );
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

            $signatureSlots = $steps->map(
                static function ($step): array {
                    $role = strtolower(trim((string) (
                        $step->role
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
                }
            )->all();

            $html = view('purchase-orders.index', [
                ...$viewData,
                'signatureSlots' => $signatureSlots,
                'serverPdf' => true,
            ])->render();

            $temporaryPdfPath = $this->pdfRenderer->render(
                $html,
                'purchase-order-' . self::TEST_PO_NUMBER . '.pdf'
            );
            $templateHash = hash_file(
                'sha256',
                $temporaryPdfPath
            );
            $finalPageNumber = count($viewData['pages']);

            $result = DB::transaction(function () use (
                $steps,
                $temporaryPdfPath,
                $templateHash,
                $finalPageNumber,
                &$storedPath
            ): array {
                $document = Document::query()->create([
                    'title' => sprintf(
                        'Purchase Order %d',
                        self::TEST_PO_NUMBER
                    ),
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
                    'file_name' => sprintf(
                        'purchase-order-%d.pdf',
                        self::TEST_PO_NUMBER
                    ),
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

            if (is_file($temporaryPdfPath)) {
                @unlink($temporaryPdfPath);
                $temporaryPdfPath = null;
            }

            $firstApproval = $result['first_approval'];

            if ($firstApproval instanceof Approval) {
                $firstApproval->loadMissing('user');

                try {
                    Mail::to($firstApproval->user->email)
                        ->queue(
                            new ApprovalPendingNotification(
                                $firstApproval
                            )
                        );
                } catch (Throwable $mailException) {
                    Log::warning(
                        'Temporary Purchase Order created, but the first approval notification could not be queued.',
                        [
                            'document_id' => $result['document']->id,
                            'approval_id' => $firstApproval->id,
                            'message' => $mailException->getMessage(),
                        ]
                    );
                }
            }

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->with(
                    'temporary_extraction_success',
                    sprintf(
                        'Purchase Order %d was generated from the database and assigned to the configured approvers.',
                        self::TEST_PO_NUMBER
                    )
                );
        } catch (ValidationException $exception) {
            if (
                is_string($storedPath) &&
                Storage::disk('local')->exists($storedPath)
            ) {
                Storage::disk('local')->delete($storedPath);
            }

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->withErrors($exception->errors())
                ->with(
                    'temporary_extraction_error',
                    collect($exception->errors())
                        ->flatten()
                        ->first()
                        ?? 'The Purchase Order could not be generated.'
                );
        } catch (Throwable $exception) {
            if (
                is_string($storedPath) &&
                Storage::disk('local')->exists($storedPath)
            ) {
                Storage::disk('local')->delete($storedPath);
            }

            Log::error(
                'Temporary Purchase Order generation failed.',
                [
                    'po_number' => self::TEST_PO_NUMBER,
                    'message' => $exception->getMessage(),
                    'exception' => $exception,
                ]
            );

            return redirect()
                ->route('dashboard', ['section' => 'workflow'])
                ->with(
                    'temporary_extraction_error',
                    'The Purchase Order could not be generated. Check the application log for details.'
                );
        } finally {
            if (
                is_string($temporaryPdfPath) &&
                is_file($temporaryPdfPath)
            ) {
                @unlink($temporaryPdfPath);
            }
        }
    }
}