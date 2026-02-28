<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\Document;
use Carbon\Carbon;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Show all pending approvals for logged-in approvers.
     */
    public function index()
    {
        // Fetch pending approvals assigned to the logged-in approver
        $approvals = Approval::with('document.uploader')
            ->where('status', 'pending')
            ->where('user_id', auth()->id()) // only show approvals for current approver
            ->get();

        return view('approvals.index', compact('approvals'));
    }

    /**
     * Approve a document.
     */
    public function approve(Approval $approval)
    {
        // Optional: restrict to specific approver
        // if ($approval->user_id !== auth()->id()) {
        //     abort(403, 'You are not authorized to approve this document.');
        // }

        // Update approval status
        $approval->update([
            'status'    => 'approved',
            'signed_at' => Carbon::now(),
        ]);

        // Sign the PDF
        $this->signPdf($approval);

        // Log the approval action
        AuditLog::create([
            'user_id'     => auth()->id(),
            'document_id' => $approval->document_id,
            'action'      => 'approved',
            'ip_address'  => request()->ip(),
        ]);

        return back()->with('success', 'Document approved successfully!');
    }

    /**
     * Optional: Sign the PDF with approver's name and timestamp
     */
    protected function signPdf(Approval $approval)
{
    $filePath = storage_path('app/public/' . $approval->document->file_path);

    if (!file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'pdf') {
        return;
    }

    $pdf = new Fpdi();
    $pdf->SetAutoPageBreak(false);
    $pageCount = $pdf->setSourceFile($filePath);

    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        if ($i === $pageCount) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetTextColor(0, 0, 200);
            $pdf->SetXY(10, $size['height'] - 10);
            $pdf->Write(0, "Approved by: " . auth()->user()->name . " on " . now()->toDateTimeString());
        }
    }

    $pdf->Output($filePath, 'F');
}
}