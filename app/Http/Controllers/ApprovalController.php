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
        $user = auth()->user();

        if ($user->role !== 'approver') {
            abort(403, 'Unauthorized');
        }

        $approvals = Approval::with(['document.uploader'])
            ->where('status', 'pending')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('approvals.index', compact('approvals'));
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

$signedPath = $this->signPdf($approval, $request, $latestPath);

        // APPROVE CURRENT STEP
        $approval->update([
            'status'    => 'approved',
            'signed_at' => now(),
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
        ->route('approvals.index')
        ->with('success', 'Document approved successfully.');
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

                    $sigImgPath = public_path('signature.png');
// DEBUG - check in laravel.log
    \Log::info('Signature image path: ' . $sigImgPath);
    \Log::info('File exists: ' . (file_exists($sigImgPath) ? 'YES' : 'NO'));
    \Log::info('sig_x=' . $sigX . ' sig_y=' . $sigY . ' sig_w=' . $sigW . ' sig_h=' . $sigH);
    \Log::info('Computed x=' . $x . ' y=' . $y . ' page_w=' . $size['width'] . ' page_h=' . $size['height']);
                    // Signature image above the line
    if (file_exists($sigImgPath)) {
        $imgW = $sigW * $size['width'];
        $imgH = $sigH * $size['height'];
        $pdf->Image($sigImgPath, $x, $y - $imgH - 2, $imgW, $imgH, 'PNG');
    }
        // Draw a line below the signature image
    $pdf->SetDrawColor(0, 0, 128);
    $pdf->SetLineWidth(0.4);
    $pdf->Line($x, $y, $x + ($sigW * $size['width']), $y);
                // Approver name in bold blue
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetTextColor(0, 0, 128);
                $pdf->SetXY($x, $y);
                $pdf->Write(0, 'Approved by: ' . auth()->user()->name);

                // Timestamp below name
                $pdf->SetXY($x, $y + 5);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(80, 80, 80);
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
}