<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\DocumentSourceProvider;
use App\Http\Controllers\Controller;
use App\Mail\ApprovalPendingNotification;
use App\Models\Approval;
use App\Models\Document;
use App\Services\DefaultApprovalWorkflowResolver;
use App\Services\DocumentSignatureBlockService;
use App\Services\PdfTemplateCompatibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TemporaryDocumentExtractionController extends Controller
{
    private const DOCUMENT_TYPE = 'purchase_order';

    public function __construct(
        private readonly DocumentSourceProvider $documentSourceProvider,
        private readonly DefaultApprovalWorkflowResolver $workflowResolver,
        private readonly PdfTemplateCompatibilityService $compatibilityService,
        private readonly DocumentSignatureBlockService $signatureBlockService,
    ) {
    }

    /**
     * Import the temporary Purchase Order and initialize
     * its configured fixed-signature approval workflow.
     */
    public function store(): RedirectResponse
    {
        $storedPath = null;

        try {
            $adminId = auth()->id();

            if (!$adminId) {
                throw new RuntimeException(
                    'The authenticated administrator could not be resolved.'
                );
            }

            /*
             * Locate the incoming Purchase Order and reference template.
             */
            $retrievedDocument =
                $this->documentSourceProvider->retrieve(
                    self::DOCUMENT_TYPE
                );

            /*
             * Load and validate the specific approvers selected
             * by the administrator.
             */
            $workflow = $this->workflowResolver->resolve(
                $retrievedDocument->templateKey
            );

            /*
             * Validate the source PDF geometry and the reference
             * template's Sejda signature fields.
             */
            $template =
                $this->compatibilityService->assertCompatible(
                    $retrievedDocument
                );

            /*
             * Store the reference-template hash as metadata.
             */
            $templateHash = hash_file(
                'sha256',
                $retrievedDocument->referenceTemplatePath
            );

            if (
                !is_string($templateHash)
                || $templateHash === ''
            ) {
                throw new RuntimeException(
                    'The reference-template hash could not be calculated.'
                );
            }

            $firstApproval = DB::transaction(
                function () use (
                    $adminId,
                    $retrievedDocument,
                    $workflow,
                    $template,
                    $templateHash,
                    &$storedPath
                ): Approval {
                    $workflowSteps = $workflow->steps
                        ->sortBy('step_order')
                        ->values();

                    if ($workflowSteps->isEmpty()) {
                        throw ValidationException::withMessages([
                            'approval_workflow' =>
                                'The Purchase Order workflow has no approvers.',
                        ]);
                    }

                    $firstWorkflowStep =
                        $workflowSteps->first();

                    if (!$firstWorkflowStep?->user_id) {
                        throw ValidationException::withMessages([
                            'approval_workflow' =>
                                'The first workflow step has no assigned user.',
                        ]);
                    }

                    /*
                     * 1. Create the document.
                     */
                    $document = Document::query()->create([
                        'title' =>
                            $retrievedDocument->title,

                        'uploaded_by' =>
                            $adminId,

                        'status' =>
                            'pending',
                    ]);

                    /*
                     * approver_id exists in the documents table but is
                     * not currently listed in Document::$fillable.
                     */
                    $document->forceFill([
                        'approver_id' =>
                            $firstWorkflowStep->user_id,
                    ])->save();

                    /*
                     * 2. Determine the private storage path.
                     */
                    $storageDirectory = config(
                        'document_extraction.'
                        . 'document_types.'
                        . self::DOCUMENT_TYPE
                        . '.storage_directory',
                        'document'
                    );

                    if (!is_string($storageDirectory)) {
                        throw new RuntimeException(
                            'The document storage directory is invalid.'
                        );
                    }

                    $storageDirectory = trim(
                        str_replace(
                            '\\',
                            '/',
                            $storageDirectory
                        ),
                        '/'
                    );

                    if (
                        $storageDirectory === ''
                        || str_contains(
                            $storageDirectory,
                            '..'
                        )
                    ) {
                        throw new RuntimeException(
                            'The document storage directory is unsafe.'
                        );
                    }

                    $storageFileName =
                        'document_'
                        . $document->id
                        . '_v1.pdf';

                    $storedPath =
                        $storageDirectory
                        . '/'
                        . $storageFileName;

                    /*
                     * 3. Copy the extracted source PDF into
                     * storage/app/private/document.
                     */
                    $sourceContents = file_get_contents(
                        $retrievedDocument->sourcePath
                    );

                    if ($sourceContents === false) {
                        throw new RuntimeException(
                            'The temporary Purchase Order could not be read.'
                        );
                    }

                    $storedSuccessfully =
                        Storage::disk('local')->put(
                            $storedPath,
                            $sourceContents
                        );

                    if (!$storedSuccessfully) {
                        throw new RuntimeException(
                            'The temporary Purchase Order could not be stored.'
                        );
                    }

                    /*
                     * 4. Create version 1 of the document file.
                     *
                     * A nonempty template_key causes the existing
                     * approval process to use fixed signature blocks.
                     */
                    $documentFile =
                        $document->files()->create([
                            'file_name' =>
                                $retrievedDocument
                                    ->originalFileName,

                            'file_path' =>
                                $storedPath,

                            'mime_type' =>
                                $retrievedDocument
                                    ->mimeType,

                            'file_size' =>
                                $retrievedDocument
                                    ->fileSize,

                            'version' =>
                                1,

                            'is_signed' =>
                                false,

                            'is_current' =>
                                true,

                            'uploaded_by' =>
                                $adminId,

                            'template_key' =>
                                $retrievedDocument
                                    ->templateKey,

                            'template_hash' =>
                                $templateHash,
                        ]);

                    /*
                     * 5. Create the ordered approval records.
                     */
                    $firstApproval = null;

                    foreach (
                        $workflowSteps
                        as $index => $workflowStep
                    ) {
                        if (!$workflowStep->user_id) {
                            throw ValidationException::withMessages([
                                'approval_workflow' =>
                                    'Workflow step '
                                    . $workflowStep->step_order
                                    . ' has no assigned user.',
                            ]);
                        }

                        $approval = Approval::query()->create([
                            'document_id' =>
                                $document->id,

                            'user_id' =>
                                $workflowStep->user_id,

                            'step_order' =>
                                $index + 1,

                            'status' =>
                                $index === 0
                                    ? 'pending'
                                    : 'waiting',

                            'received_at' =>
                                $index === 0
                                    ? now()
                                    : null,
                        ]);

                        if ($index === 0) {
                            $firstApproval = $approval;
                        }
                    }

                    if (!$firstApproval) {
                        throw new RuntimeException(
                            'The first approval record could not be created.'
                        );
                    }

                    /*
                     * 6. Create the backend-controlled fixed
                     * signature blocks for all approvers.
                     */
                    $this->signatureBlockService
                        ->createFromExistingApprovals(
                            document: $document,
                            documentFile: $documentFile,
                            template: $template
                        );

                    return $firstApproval;
                }
            );
        } catch (ValidationException $exception) {
            $this->deleteStoredFile(
                $storedPath
            );

            return redirect()
                ->route('dashboard', [
                    'section' => 'workflow',
                ])
                ->withErrors(
                    $exception->errors()
                );
        } catch (Throwable $exception) {
            $this->deleteStoredFile(
                $storedPath
            );

            report($exception);

            return redirect()
                ->route('dashboard', [
                    'section' => 'workflow',
                ])
                ->with(
                    'document_error',
                    'The temporary Purchase Order could not be imported.'
                );
        }

        /*
         * Queue the notification only after the document transaction
         * has committed. Notification failure must not remove a
         * successfully imported document.
         */
        try {
            $firstApproval->loadMissing('user');

            if (!$firstApproval->user) {
                throw new RuntimeException(
                    'The first approver could not be resolved.'
                );
            }

            Mail::to(
                $firstApproval->user->email
            )->queue(
                new ApprovalPendingNotification(
                    $firstApproval
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard', [
                    'section' => 'workflow',
                ])
                ->with(
                    'document_success',
                    'Temporary Purchase Order imported successfully, '
                    . 'but the first-approver email could not be queued.'
                );
        }

        return redirect()
            ->route('dashboard', [
                'section' => 'workflow',
            ])
            ->with(
                'document_success',
                'Temporary Purchase Order imported successfully.'
            );
    }

    /**
     * Delete a stored PDF after a failed database transaction.
     */
    private function deleteStoredFile(
        ?string $storedPath
    ): void {
        if (
            is_string($storedPath)
            && $storedPath !== ''
            && Storage::disk('local')->exists(
                $storedPath
            )
        ) {
            Storage::disk('local')->delete(
                $storedPath
            );
        }
    }
}