<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\DocumentSignatureBlock;
use App\Services\DocumentSignatureBlockService;
use Illuminate\Validation\ValidationException;
use App\Services\PdfNormalizationService;
use App\Mail\ApprovalPendingNotification;
use App\Mail\ApprovalProgressNotification;
use App\Mail\ApprovalCompletedNotification;
use App\Mail\ApprovalRejectedNotification;

class ApprovalController extends Controller
{
     public function __construct(
    private readonly DocumentSignatureBlockService $signatureBlockService,
    private readonly PdfNormalizationService $pdfNormalizationService
) {
}

    public function index() {
        return redirect()->route('dashboard');
    }

    public function approve(Request $request, Approval $approval) {

        if ($approval->user_id !== auth()->id()) {
            abort(403, 'Unauthorized approval.');
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This approval is not active.');
        }

        try {
            \DB::transaction(function () use ($request, $approval, &$signedPath) {

                // Retrieve the latest document version.
$currentFile = $approval->document
    ->latestVersion();

if (!$currentFile) {
    throw ValidationException::withMessages([
        'document' =>
            'The document file could not be found.',
    ]);
}

$latestPath = storage_path(
    'app/private/' .
    $currentFile->generated_storage_path
);

/*
 * Purchase Orders use fixed backend-controlled
 * signature blocks.
 *
 * Other PDFs continue using manual coordinates
 * submitted by the browser.
 */
$usesFixedSignatureBlock =
    is_string($currentFile->template_key) &&
    trim($currentFile->template_key) !== '';

$signatureBlock = null;

if ($usesFixedSignatureBlock) {
    /*
     * Lock the assigned block while signing to prevent
     * two simultaneous approval requests.
     */
    $signatureBlock = $approval
        ->signatureBlock()
        ->lockForUpdate()
        ->first();

    if (!$signatureBlock) {
        throw ValidationException::withMessages([
            'signature_block' =>
                'No fixed signature block is assigned to this approval.',
        ]);
    }

    if (
        (int) $signatureBlock->assigned_user_id !==
        (int) auth()->id()
    ) {
        throw ValidationException::withMessages([
            'signature_block' =>
                'This signature block is assigned to another user.',
        ]);
    }

    if (
        $signatureBlock->status !==
        DocumentSignatureBlock::STATUS_AVAILABLE
    ) {
        throw ValidationException::withMessages([
            'signature_block' =>
                'This fixed signature block is not currently available.',
        ]);
    }

    /*
     * Ignore any browser-submitted coordinates.
     */
    $signatureCoordinates = [
        'x' =>
            (float) $signatureBlock->x,

        'y' =>
            (float) $signatureBlock->y,

        'width' =>
            (float) $signatureBlock->width,

        'height' =>
            (float) $signatureBlock->height,

        'page' =>
            (int) $signatureBlock->page_number,
    ];
} else {
    /*
     * Normal PDF: continue accepting manually selected
     * browser coordinates.
     */
    $validatedCoordinates = $request->validate([
        'sig_x' => [
            'required',
            'numeric',
            'between:0,1',
        ],

        'sig_y' => [
            'required',
            'numeric',
            'between:0,1',
        ],

        'sig_w' => [
            'required',
            'numeric',
            'between:0.001,1',
        ],

        'sig_h' => [
            'required',
            'numeric',
            'between:0.001,1',
        ],

        'sig_page' => [
            'required',
            'integer',
            'min:1',
        ],
    ]);

    $signatureCoordinates = [
        'x' =>
            (float) $validatedCoordinates['sig_x'],

        'y' =>
            (float) $validatedCoordinates['sig_y'],

        'width' =>
            (float) $validatedCoordinates['sig_w'],

        'height' =>
            (float) $validatedCoordinates['sig_h'],

        'page' =>
            (int) $validatedCoordinates['sig_page'],
    ];

    /*
     * Prevent the manual signature rectangle from
     * extending beyond the PDF page.
     */
    if (
        $signatureCoordinates['x'] +
        $signatureCoordinates['width'] > 1
    ) {
        throw ValidationException::withMessages([
            'signature' =>
                'The selected signature position extends beyond the page width.',
        ]);
    }

    if (
        $signatureCoordinates['y'] +
        $signatureCoordinates['height'] > 1
    ) {
        throw ValidationException::withMessages([
            'signature' =>
                'The selected signature position extends beyond the page height.',
        ]);
    }

    /*
     * Overlap checking remains active for normal PDFs.
     */
    $this->validateSignatureOverlap(
        $approval,
        $signatureCoordinates
    );
}

$signedPath = $this->signPdf(
    $approval,
    $signatureCoordinates,
    $latestPath
);

if (!$signedPath) {
    throw ValidationException::withMessages([
        'document' =>
            'The signed PDF could not be generated.',
    ]);
}

                $currentVersion = $approval->document
                    ->latestVersion();

                $currentFiles = $approval->document
                    ->files()
                    ->where('is_current', true)
                    ->get();

                foreach ($currentFiles as $file) {
                    $file->update([
                        'is_current' => false
                    ]);
                }
            
                $newFile = $approval->document
                    ->files()
                    ->create([

                        'parent_file_id' => $currentVersion?->id,

                        'file_name' => basename($signedPath),

                        'file_path' => $signedPath,

                        'mime_type' => 'application/pdf',

                        'file_size' => null,

                        'version' => ($currentVersion?->version ?? 0) + 1,

                        'is_signed' => true,

                        'is_current' => true,

                        'uploaded_by' => auth()->id(),
                        'template_key' =>
                            $currentFile->template_key,

                        'template_hash' =>
                            $currentFile->template_hash,

                    ]);

                    // APPROVE CURRENT STEP
                    $completedAt = now();
                    $approval->update([
                        'status'    => 'approved',
                        'signed_at' => $completedAt,

                        'completed_at' => $completedAt,
                        'duration_seconds' => $approval->received_at
                        ? (int) round($approval->received_at->diffInSeconds($completedAt))
                        : null,

                        'sig_x' =>
                            $signatureCoordinates['x'],

                        'sig_y' =>
                            $signatureCoordinates['y'],

                        'sig_w' =>
                            $signatureCoordinates['width'],

                        'sig_h' =>
                            $signatureCoordinates['height'],

                        'sig_page' =>
                            $signatureCoordinates['page'],
                    ]);

                    /*
 * Mark the fixed Purchase Order block as signed.
 *
 * For normal PDFs, $signatureBlock remains null and
 * no block update is performed.
 */
if ($signatureBlock) {
    $signatureBlock->update([
        'status' =>
            DocumentSignatureBlock::STATUS_SIGNED,

        'signed_by_user_id' =>
            auth()->id(),

        'signed_document_file_id' =>
            $newFile->id,

        'signed_at' =>
            $completedAt,
    ]);
}

                    $document = $approval->document;

                    Mail::to($document->uploader->email)->queue(new ApprovalProgressNotification($approval));

                    $approval->refresh();

                    // FIND NEXT WAITING APPROVER
                    $nextApproval = Approval::where('document_id', $approval->document_id)
                        ->where('status', 'waiting')
                        ->where('step_order', '>', $approval->step_order)
                        ->orderBy('step_order', 'asc')
                        ->first();

                    // IF NEXT APPROVER EXISTS
                    if ($nextApproval) {

                        $nextApproval->update([
                            'status' => 'pending',
                            'received_at' => now(),
                        ]);

                        /*
                        * Purchase Order only:
                        * unlock the fixed block assigned to the next approver.
                        */
                        if ($usesFixedSignatureBlock) {
                            $this->signatureBlockService
                                ->activateForApproval(
                                    $nextApproval->id
                                );
                        }

                        Mail::to($nextApproval->user->email)->queue(new ApprovalPendingNotification($nextApproval));

                        $document = $approval->document;

                        $document->update([
                            'status'      => 'in_progress',
                            'approver_id' => $nextApproval->user_id,
                        ]);

                    } else {

                        $document = $approval->document;
                        $document->update([
                            'status'          => 'approved',
                            'admin_signed_at' => now(),
                            'approver_id'     => null,
                        ]);

                        Mail::to($document->uploader->email)->queue(new ApprovalCompletedNotification($document));
                    }
            });

            return redirect()
                ->route('dashboard', ['section' => 'documents'])
                ->with('approval_success', 'Document approved successfully.');

        } catch (
    \Illuminate\Validation\ValidationException $e
) {
    return redirect()
        ->route('dashboard', [
            'section' => 'documents',
        ])
        ->withErrors(
            $e->errors(),
            'approval'
        )
        ->with(
            'approval_error',
            collect($e->errors())
                ->flatten()
                ->first()
        );
} catch (\Throwable $e) {
    Log::error(
        'Approval signing failed',
        [
            'approval_id' => $approval->id,
            'document_id' => $approval->document_id,
            'user_id' => auth()->id(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]
    );

    return redirect()
        ->route('dashboard', [
            'section' => 'documents',
        ])
        ->with(
            'approval_error',
            app()->isLocal()
                ? 'Approval failed: ' .
                    $e->getMessage()
                : 'Something went wrong while approving the document.'
        );
}
    }

    public function reject(Request $request, Approval $approval) {

        if ($approval->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // MUST BE PENDING
        if ($approval->status !== 'pending') {
            return back()->with(
                'error',
                'This approval is not active.'
            );
        }

        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        \DB::transaction(function () use ($approval, $request) {

            // CURRENT APPROVAL = REJECTED
            $completedAt = now();

            $approval->update([
                'status' => 'rejected',

                'remarks' => $request->remarks,

                'rejected_at' => $completedAt,

                'completed_at' => $completedAt,

                'duration_seconds' => $approval->received_at
                ? (int) round($approval->received_at->diffInSeconds($completedAt))
                : null,
            ]);

            // DOCUMENT = REJECTED
            $approval->document()->update([
                'status'      => 'rejected',
                'approver_id' => null,
            ]);

            // OPTIONAL:
            // Cancel remaining waiting approvals
            Approval::where('document_id', $approval->document_id)
                ->where('id', '!=', $approval->id)
                ->whereIn('status', ['waiting', 'pending'])
                ->update([
                    'status' => 'cancelled'
                ]);

                /*
 * Cancel every unsigned fixed signature block.
 *
 * Signed blocks remain signed.
 * Normal PDFs have no signature blocks, so this
 * safely updates zero records.
 */
$this->signatureBlockService
    ->cancelRemaining(
        $approval->document
    );
        });

        // Send rejection notification
        \Mail::to($approval->user)->send(new ApprovalRejectedNotification($approval->document));

        return redirect()
            ->route('dashboard', ['section' => 'documents'])
            ->with('approval_success', 'Document rejected successfully.');
    
    }

    protected function signPdf(
    Approval $approval,
    array $coordinates,
    string $inputPath
): ?string {
    if (!file_exists($inputPath)) {
        return null;
    }

    $normalizedInputPath =
    $this->pdfNormalizationService
        ->normalizeForFpdi($inputPath);

try {

    $pdf = new Fpdi();
    $pdf->SetAutoPageBreak(false);

  $pageCount = $pdf->setSourceFile(
    $normalizedInputPath
);

    $sigX = (float) $coordinates['x'];
    $sigY = (float) $coordinates['y'];
    $sigW = (float) $coordinates['width'];
    $sigH = (float) $coordinates['height'];
    $sigPage = (int) $coordinates['page'];

    if (
        $sigPage < 1 ||
        $sigPage > $pageCount
    ) {
        throw ValidationException::withMessages([
            'signature' =>
                'The selected signature page does not exist in the PDF.',
        ]);
    }
$sourceFile = $approval->document->latestVersion();

$isFixedTemplate =
    is_string($sourceFile?->template_key) &&
    trim($sourceFile->template_key) !== '';
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl  = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage(
                $size['width'] > $size['height'] ? 'L' : 'P',
                [$size['width'], $size['height']]
            );
            $pdf->useTemplate($tpl);

            if ($i === $sigPage) {
    $x = $sigX * $size['width'];
    $y = $sigY * $size['height'];

    $blockW = $sigW * $size['width'];
    $blockH = $sigH * $size['height'];

    $sigImgPath = auth()->user()->signature_path
        ? storage_path(
            'app/private/' .
            auth()->user()->signature_path
        )
        : null;

    if (
        !$sigImgPath ||
        !file_exists($sigImgPath)
    ) {
        throw ValidationException::withMessages([
            'signature' =>
                'Your saved signature image could not be found.',
        ]);
    }

    /*
     * Fixed PDF template:
     * render only the signature image inside the
     * exact Sejda signature-field rectangle.
     */
    if ($isFixedTemplate) {
        $imageSize = getimagesize($sigImgPath);

        if ($imageSize === false) {
            throw ValidationException::withMessages([
                'signature' =>
                    'The saved signature image is invalid.',
            ]);
        }

        $imagePixelWidth = (float) $imageSize[0];
        $imagePixelHeight = (float) $imageSize[1];

        /*
         * Small internal padding prevents the signature
         * from touching the field boundary.
         */
        $padding = min($blockW, $blockH) * 0.08;

        $availableW = max(
            $blockW - ($padding * 2),
            0.1
        );

        $availableH = max(
            $blockH - ($padding * 2),
            0.1
        );

        $scale = min(
            $availableW / $imagePixelWidth,
            $availableH / $imagePixelHeight
        );

        $imgW = $imagePixelWidth * $scale;
        $imgH = $imagePixelHeight * $scale;

        $imgX =
            $x +
            (($blockW - $imgW) / 2);

        $imgY =
            $y +
            (($blockH - $imgH) / 2);

        $pdf->Image(
            $sigImgPath,
            $imgX,
            $imgY,
            $imgW,
            $imgH,
            'PNG'
        );

        continue;
    }

    /*
     * Ordinary PDFs retain the existing display:
     * signature, line, name, and timestamp.
     */
    $imgW = $blockW * 0.65;
    $imgH = 18;

    $pdf->Image(
        $sigImgPath,
        $x,
        $y,
        $imgW,
        $imgH,
        'PNG'
    );

    $lineY = $y + $imgH + 4;

    $pdf->SetDrawColor(0, 0, 128);
    $pdf->SetLineWidth(0.4);
    $pdf->Line(
        $x,
        $lineY,
        $x + $blockW,
        $lineY
    );

    $pdf->SetFont(
        'helvetica',
        'B',
        10
    );

    $pdf->SetTextColor(0, 0, 128);
    $pdf->SetXY($x, $lineY + 2);
    $pdf->Write(
        0,
        auth()->user()->name
    );

    $pdf->SetFont(
        'helvetica',
        '',
        8
    );

    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetXY($x, $lineY + 7);

    $pdf->Write(
        0,
        Carbon::now()->format(
            'Y-m-d H:i:s'
        )
    );
}
        }

        // Save as a NEW signed file — never overwrite the original
        $currentVersion = $approval->document->latestVersion();

        $newVersion = ($currentVersion?->version ?? 0) + 1;

        $signedFileName =
            'document_' .
            $approval->document_id .
            '_v' .
            $newVersion .
            '_signed.pdf';

        $signedRelativePath =
            'document/' .
            $signedFileName;

        $signedAbsolutePath = storage_path(
            'app/private/' . $signedRelativePath
        );

        // Ensure the signed/ directory exists
        $signedDir = dirname($signedAbsolutePath);
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0755, true);
        }

        $pdf->Output($signedAbsolutePath, 'F');

        return $signedRelativePath;

} finally {
    if (is_file($normalizedInputPath)) {
        @unlink($normalizedInputPath);
    }
}
    }

    protected function validateSignatureOverlap(
    Approval $approval,
    array $coordinates
): void {

        $x =
    (float) $coordinates['x'];

$y =
    (float) $coordinates['y'];

$w =
    (float) $coordinates['width'];

$h =
    (float) $coordinates['height'];

$page =
    (int) $coordinates['page'];
        $existingSignatures = Approval::where('document_id', $approval->document_id)
            ->where('status', 'approved')
            ->whereNotNull('sig_x')
            ->get();

        foreach ($existingSignatures as $existing) {
            if ((int) $existing->sig_page !== $page) {
                continue;
            }

            $existingX = (float) $existing->sig_x;
            $existingY = (float) $existing->sig_y;
            $existingW = (float) $existing->sig_w;
            $existingH = (float) $existing->sig_h;

            $hasOverlap = !(
                $x + $w <= $existingX ||
                $x >= $existingX + $existingW ||
                $y + $h <= $existingY ||
                $y >= $existingY + $existingH
            );

            if ($hasOverlap) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'signature' => 'Signature overlaps with an existing approver signature. Please choose another position.',
                ]);
            }
        }
    }
}