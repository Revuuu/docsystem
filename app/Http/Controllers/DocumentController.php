<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Approval;
use App\Mail\ApprovalPendingNotification;
use Illuminate\Support\Facades\Mail;
use App\Services\DocumentSignatureBlockService;
use App\Services\SignatureTemplateResolver;
use App\Services\PdfFormFieldService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DocumentController extends Controller
{
    /** 
     * Store uploaded document 
     */

        public function __construct(
    private readonly SignatureTemplateResolver $templateResolver,
    private readonly DocumentSignatureBlockService $signatureBlockService,
    private readonly PdfFormFieldService $pdfFormFieldService
) {
}

    public function store(Request $request)
{
    $request->validate(
        [
            'title' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:10240',
            'approvers' => 'required|array|min:1',
            'approvers.*' => 'exists:users,id',
        ],
        [
            'file.max' =>
                'The uploaded file exceeds the maximum allowed size of 10 MB.',

            'file.required' =>
                'Please select a PDF file.',

            'file.mimes' =>
                'Only PDF files are allowed.',
        ]
    );

    $uploadedFile = $request->file('file');

    /*
     * Detect the supported PDF template before creating
     * any database records.
     */
    $template = $this->templateResolver->resolve(
        $uploadedFile
    );

    if ($template) {
    $requiredFieldNames = collect(
        $template['blocks'] ?? []
    )
        ->pluck('field_name')
        ->filter(
            fn ($fieldName): bool =>
                is_string($fieldName) &&
                trim($fieldName) !== ''
        )
        ->map(
            fn (string $fieldName): string =>
                trim($fieldName)
        )
        ->unique()
        ->values()
        ->all();

    $this->pdfFormFieldService
        ->validateSignatureFields(
            pdfPath: $uploadedFile->getRealPath(),
            requiredFieldNames: $requiredFieldNames
        );
}

    $hierarchy = [
        'staff' => 1,
        'supervisor' => 2,
        'depthead' => 3,
        'division' => 4,
        'executive' => 5,
    ];

    $approvers = User::query()
        ->with('roles')
        ->whereIn('id', $request->approvers)
        ->where('id', '!=', Auth::id())
        ->get()
        ->sortBy(function (User $approver) use (
            $hierarchy
        ) {
            $approverRole =
                $approver->roles->first()?->name
                ?? $approver->role;

            return $hierarchy[$approverRole] ?? 999;
        })
        ->values();

    if ($approvers->isEmpty()) {
        return redirect()
            ->route('dashboard')
            ->with(
                'document_error',
                'Please select at least one valid approver.'
            )
            ->with('section', 'documents');
    }

    /*
 * Apply role restrictions only when the uploaded PDF
 * matches a fixed signature template.
 *
 * Normal PDFs skip this validation and continue using
 * manually selected browser coordinates.
 */
if ($template) {
    $supportedRoles = collect(
        $template['blocks'] ?? []
    )
        ->pluck('role')
        ->filter()
        ->values()
        ->all();

    /*
     * Resolve each selected approver's role.
     */
    $approverRoles = $approvers->mapWithKeys(
        function (User $approver): array {
            $role =
                $approver->roles->first()?->name
                ?? $approver->role;

            return [
                $approver->id => $role,
            ];
        }
    );

    /*
     * Ensure every selected approver has a fixed block
     * in the detected template.
     */
    $unsupportedApprovers = $approvers->filter(
        function (User $approver) use (
            $approverRoles,
            $supportedRoles
        ): bool {
            $role = $approverRoles->get(
                $approver->id
            );

            return !in_array(
                $role,
                $supportedRoles,
                true
            );
        }
    );

    if ($unsupportedApprovers->isNotEmpty()) {
        $names = $unsupportedApprovers
            ->pluck('name')
            ->implode(', ');

        return redirect()
            ->route('dashboard')
            ->with(
                'document_error',
                "The following approvers do not have a configured Purchase Order signature block: {$names}."
            )
            ->with('section', 'documents');
    }

    /*
     * The Purchase Order template contains one fixed
     * position for each role.
     */
    $duplicateRoles = $approverRoles
        ->values()
        ->duplicates()
        ->unique()
        ->values();

    if ($duplicateRoles->isNotEmpty()) {
        return redirect()
            ->route('dashboard')
            ->with(
                'document_error',
                'Only one approver may be selected for each Purchase Order role. Duplicate roles: ' .
                $duplicateRoles->implode(', ')
            )
            ->with('section', 'documents');
    }
}

    $storedPath = null;

    try {
        $result = DB::transaction(
            function () use (
                $request,
                $uploadedFile,
                $approvers,
                $template,
                &$storedPath
            ): array {
                /*
                 * 1. Create the document.
                 */
                $document = Document::query()->create([
                    'title' => $request->title,
                    'uploaded_by' => Auth::id(),
                    'approver_id' =>
                        $approvers->first()->id,
                    'status' => 'pending',
                ]);

                /*
                 * 2. Store the original PDF.
                 */
                $storageFileName =
                    'document_' .
                    $document->id .
                    '_v1.pdf';

                $storedPath =
                    'document/' .
                    $storageFileName;

                Storage::disk('local')->put(
                    $storedPath,
                    file_get_contents(
                        $uploadedFile->getRealPath()
                    )
                );

                /*
                 * 3. Create the DocumentFile record.
                 */
                $documentFile = $document->files()->create([
    'file_name' =>
        $uploadedFile->getClientOriginalName(),

    'file_path' =>
        $storedPath,

    'mime_type' =>
        $uploadedFile->getMimeType(),

    'file_size' =>
        $uploadedFile->getSize(),

    'version' => 1,

    'is_signed' => false,

    'is_current' => true,

    'uploaded_by' => Auth::id(),

    /*
     * Purchase Order:
     * template_key = purchase_order
     *
     * Normal PDF:
     * template_key = null
     */
    'template_key' =>
        $template['key'] ?? null,

    'template_hash' =>
        $template['uploaded_hash'] ?? null,
]);
                /*
                 * 4. Create all approval records.
                 */
                $firstApproval = null;

                foreach (
                    $approvers as $index => $approver
                ) {
                    $approval =
                        Approval::query()->create([
                            'document_id' =>
                                $document->id,

                            'user_id' =>
                                $approver->id,

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

                /*
 * 5. Create fixed signature blocks only when
 * the uploaded PDF matches a configured template.
 *
 * Normal PDFs will keep using browser-controlled
 * manual signature coordinates.
 */
if ($template) {
    $this->signatureBlockService
        ->createFromExistingApprovals(
            document: $document,
            documentFile: $documentFile,
            template: $template,
        );
}

                return [
                    'document' => $document,
                    'first_approval' =>
                        $firstApproval,
                ];
            }
        );

        /*
         * Queue the email after the database transaction
         * completes successfully.
         */
        $firstApproval =
            $result['first_approval'];

        if ($firstApproval) {
            $firstApproval->loadMissing('user');

            Mail::to(
                $firstApproval->user->email
            )->queue(
                new ApprovalPendingNotification(
                    $firstApproval
                )
            );
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'document_success',
                'Document uploaded successfully!'
            )
            ->with('section', 'documents');
    } catch (ValidationException $e) {
        if (
            $storedPath &&
            Storage::disk('local')->exists(
                $storedPath
            )
        ) {
            Storage::disk('local')->delete(
                $storedPath
            );
        }

        throw $e;
    } catch (Throwable $e) {
        if (
            $storedPath &&
            Storage::disk('local')->exists(
                $storedPath
            )
        ) {
            Storage::disk('local')->delete(
                $storedPath
            );
        }

        report($e);

        return redirect()
            ->route('dashboard')
            ->with(
                'document_error',
                'Something went wrong while uploading the document.'
            )
            ->with('section', 'documents');
    }
}
}