<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;

class ApprovalController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

 public function approve(Request $request, Approval $approval)
{
    
    // SECURITY CHECKS
    if ($approval->user_id !== auth()->id()) {
        abort(403, 'Unauthorized approval.');
    }

    if ($approval->status !== 'pending') {
        return back()->with('error', 'This approval is not active.');
    }

    \DB::transaction(function () use ($request, $approval, &$signedPath) {

        // Generate signed PDF
        $latestPath = $approval->document->signed_file_path
    ? storage_path('app/public/' . $approval->document->signed_file_path)
    : storage_path('app/public/' . $approval->document->file_path);

    
    $this->validateSignatureOverlap($approval, $request);
    $signedPath = $this->signPdf($approval, $request, $latestPath);

        // APPROVE CURRENT STEP
        $approval->update([
    'status'    => 'approved',
    'signed_at' => now(),

    'sig_x'     => $request->sig_x,
    'sig_y'     => $request->sig_y,

    'sig_w'     => $request->sig_w,
    'sig_h'     => $request->sig_h,

    'sig_page'  => $request->sig_page,
]);

        // IMPORTANT: reload fresh data
        $approval->refresh();

        // FIND NEXT WAITING APPROVER
        $nextApproval = Approval::where('document_id', $approval->document_id)
            ->where('status', 'waiting')
            ->where('step_order', '>', $approval->step_order)
            ->orderBy('step_order', 'asc')
            ->first();

        // IF NEXT APPROVER EXISTS
        // In the "next approver exists" branch, also update signed_file_path:
        if ($nextApproval) {
            $nextApproval->update(['status' => 'pending']);

            $approval->document()->update([
                'status'           => 'in_progress',
                'approver_id'      => $nextApproval->user_id,
                'signed_file_path' => $signedPath, // ← ADD THIS
            ]);

        } else {
            // Final approval — this part stays the same
            $approval->document()->update([
                'status'           => 'approved',
                'signed_file_path' => $signedPath,
                'admin_signed_at'  => now(),
                'approver_id'      => null,
            ]);
        }

        // AUDIT LOG
        AuditLog::create([
            'user_id'     => auth()->id(),
            'document_id' => $approval->document_id,
            'action'      => 'approved',
            'ip_address'  => $request->ip(),
        ]);
    });

    return redirect()
    ->route('dashboard')
    ->with('success', 'Document approved successfully.');
}
public function reject(Request $request, Approval $approval)
{
    // SECURITY
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
        $approval->update([
            'status'       => 'rejected',
            'remarks'      => $request->remarks,
            'rejected_at'  => now(),
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

        // AUDIT LOG
        AuditLog::create([
            'user_id'     => auth()->id(),
            'document_id' => $approval->document_id,
            'action'      => 'rejected',
            'ip_address'  => $request->ip(),
        ]);

    });

    return redirect()
        ->route('dashboard')
        ->with(
            'success',
            'Document rejected successfully.'
        );
}

    protected function signPdf(Approval $approval, Request $request, string $inputPath): ?string
    {
        

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
                    ? storage_path('app/public/' . auth()->user()->signature_path)
                    : null;
// DEBUG - check in laravel.log
    \Log::info('Signature image path: ' . $sigImgPath);
    \Log::info('File exists: ' . (file_exists($sigImgPath) ? 'YES' : 'NO'));
    \Log::info('sig_x=' . $sigX . ' sig_y=' . $sigY . ' sig_w=' . $sigW . ' sig_h=' . $sigH);
    \Log::info('Computed x=' . $x . ' y=' . $y . ' page_w=' . $size['width'] . ' page_h=' . $size['height']);
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
        $signedRelativePath = 'documents/signed/signed_step' . $approval->step_order . '_' . basename($approval->document->file_path);
        $signedAbsolutePath = storage_path('app/public/' . $signedRelativePath);

        // Ensure the signed/ directory exists
        $signedDir = dirname($signedAbsolutePath);
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0755, true);
        }

        $pdf->Output($signedAbsolutePath, 'F');

        return $signedRelativePath;
    }
   protected function validateSignatureOverlap(Approval $approval, Request $request)
{
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