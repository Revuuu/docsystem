<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprovalPendingNotification;
use App\Mail\ApprovalProgressNotification;
use App\Mail\ApprovalCompletedNotification;

class ApprovalController extends Controller
{
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

                // Generate signed PDF
                $currentFile = $approval->document->latestVersion();

                $latestPath = storage_path(
                    'app/private/' . $currentFile->generated_storage_path
                );

            
                $this->validateSignatureOverlap($approval, $request);

                $signedPath = $this->signPdf(
                    $approval,
                    $request,
                    $latestPath
                );

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

                    ]);

                    // APPROVE CURRENT STEP
                    $completedAt = now();
                    $approval->update([
                        'status'    => 'approved',
                        'signed_at' => $completedAt,

                        'completed_at' => $completedAt,
                        'duration_seconds' => $approval->received_at
                            ? $approval->received_at->diffInSeconds($completedAt)
                            : null,

                        'sig_x'     => $request->sig_x,
                        'sig_y'     => $request->sig_y,

                        'sig_w'     => $request->sig_w,
                        'sig_h'     => $request->sig_h,

                        'sig_page'  => $request->sig_page,
                    ]);

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('dashboard', ['section' => 'documents'])
                ->withErrors($e->errors(), 'approval')
                ->with('approval_error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard')
                ->with('approval_error', 'Something went wrong while approving the document.')
                ->with('section', 'documents');
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
                    ? $approval->received_at->diffInSeconds($completedAt)
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
        });

        return redirect()
            ->route('dashboard', ['section' => 'documents'])
            ->with('approval_success', 'Document rejected successfully.');
    }

    protected function signPdf(Approval $approval, Request $request, string $inputPath): ?string {

        if (!file_exists($inputPath)) {
            return null;
        }

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($inputPath);

        $sigX    = (float) ($request->sig_x    ?? 0.1);
        $sigY    = (float) ($request->sig_y    ?? 0.8);
        $sigW    = (float) ($request->sig_w    ?? 0.2);
        $sigH    = (float) ($request->sig_h    ?? 0.05);
        $sigPage = (int)   ($request->sig_page ?? 1);

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

                $sigImgPath = auth()->user()->signature_path
                    ? storage_path('app/private/' . auth()->user()->signature_path)
                    : null;

                // Signature image above the line
                $blockW = $sigW * $size['width'];

                $imgW = $blockW * 0.65;
                $imgH = 18;

                if ($sigImgPath && file_exists($sigImgPath)) {
                    $pdf->Image($sigImgPath, $x, $y, $imgW, $imgH, 'PNG');
                }

                $lineY = $y + $imgH + 4;

                $pdf->SetDrawColor(0, 0, 128);
                $pdf->SetLineWidth(0.4);
                $pdf->Line($x, $lineY, $x + $blockW, $lineY);

                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetTextColor(0, 0, 128);
                $pdf->SetXY($x, $lineY + 2);
                $pdf->Write(0, auth()->user()->name);

                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->SetXY($x, $lineY + 7);
                $pdf->Write(0, Carbon::now()->format('Y-m-d H:i:s'));
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
    }

    protected function validateSignatureOverlap(Approval $approval, Request $request) {

        $x = (float) $request->sig_x;
        $y = (float) $request->sig_y;
        $w = (float) $request->sig_w;
        $h = (float) $request->sig_h;
        $page = (int) $request->sig_page;

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