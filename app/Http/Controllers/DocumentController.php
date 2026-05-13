<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Approval;

class DocumentController extends Controller
{
    /** 
     * Store uploaded document 
     */
    public function store(Request $request)
{
    $request->validate([

        'title' => 'required|string|max:255',

        'file' => 'required|mimes:pdf|max:10240',

        'approvers' => 'required|array|min:1',

        'approvers.*' => 'exists:users,id',

    ]);

    // Upload PDF
    $path = $request->file('file')->store('documents', 'public');

    // Create document
    $document = Document::create([

        'title'       => $request->title,

        'file_path'   => $path,

        'uploaded_by' => Auth::id(),

        // FIRST approver becomes initial approver
        'approver_id' => $request->approvers[0],

        'status'      => 'pending',
    ]);

    // Create approval workflow
    foreach ($request->approvers as $index => $approverId) {

    Approval::create([
        'document_id' => $document->id,
        'user_id'     => $approverId,

        // IMPORTANT: workflow order
        'step_order'  => $index + 1,

        // first approver starts, others wait
        'status'      => $index === 0 ? 'pending' : 'waiting',
    ]);

}

    return redirect()
        ->route('dashboard')
        ->with('success', 'Document uploaded successfully!');
}

    /**
     * Admin signs document with dynamic placement.
     *
     * Expects POST fields (all optional — falls back to bottom-right if absent):
     *   sig_x    float  0.0–1.0  horizontal position ratio across page width
     *   sig_y    float  0.0–1.0  vertical position ratio down page height
     *   sig_w    float  0.0–1.0  signature box width ratio
     *   sig_h    float  0.0–1.0  signature box height ratio
     *   sig_page int    1-based  page number to stamp (defaults to last page)
     */
    public function adminSign(Request $request, Document $document)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($document->admin_signed_at !== null) {
            return redirect()->route('dashboard')
                ->with('error', 'Document already signed.');
        }

        $inputPath      = storage_path('app/public/' . $document->file_path);
        $signedName     = 'signed_' . basename($document->file_path);
        $signedRelative = 'documents/signed/' . $signedName;
        $outputPath     = storage_path('app/public/documents/signed/' . $signedName);
        $signaturePath  = storage_path('app/public/signature.png');
        $signerName     = Auth::user()->name;
        $dateStr        = now()->format('F d, Y h:i A');

        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // ── Read placement values sent by the modal ──────────────────────────
        $hasPlacement = $request->filled('sig_x') && $request->filled('sig_y');

        $sigXRatio = $hasPlacement ? (float) $request->input('sig_x') : null;
        $sigYRatio = $hasPlacement ? (float) $request->input('sig_y') : null;
        $sigWRatio = $hasPlacement ? (float) $request->input('sig_w') : null;
        $sigHRatio = $hasPlacement ? (float) $request->input('sig_h') : null;
        $sigPage   = $request->filled('sig_page') ? (int) $request->input('sig_page') : null;

        try {
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $pdf->SetAutoPageBreak(false);

            $pageCount = $pdf->setSourceFile($inputPath);

            if ($sigPage === null || $sigPage < 1 || $sigPage > $pageCount) {
                $sigPage = $pageCount;
            }

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl  = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);

                $pdf->AddPage(
                    $size['width'] > $size['height'] ? 'L' : 'P',
                    [$size['width'], $size['height']]
                );
                $pdf->useTemplate($tpl);

                if ($i === $sigPage) {
                    // ── Resolve box dimensions in PDF points ─────────────────
                    if ($hasPlacement) {
                        $boxW = $sigWRatio * $size['width'];
                        $boxH = $sigHRatio * $size['height'];
                        $boxX = $sigXRatio * $size['width'];
                        $boxY = $sigYRatio * $size['height'];

                        // Ensure box doesn't bleed off the page edge
                        $boxX = min($boxX, $size['width']  - $boxW);
                        $boxY = min($boxY, $size['height'] - $boxH);
                        $boxX = max($boxX, 0);
                        $boxY = max($boxY, 0);
                    } else {
                        // ── Fallback: original bottom-right position ──────────
                        $boxW = 70;
                        $boxH = 28;
                        $boxX = $size['width']  - 75;
                        $boxY = $size['height'] - 35;
                    }

                    // ── Draw border box ───────────────────────────────────────
                    $pdf->SetDrawColor(180, 180, 180);
                    $pdf->SetLineWidth(0.3);
                    $pdf->Rect($boxX, $boxY, $boxW, $boxH);

                    // ── Signature image ───────────────────────────────────────
                    if (file_exists($signaturePath)) {
                        $imgH = $boxH * 0.55;
                        $imgW = $boxW - 4;
                        $imgX = $boxX + 2;
                        $imgY = $boxY + 2;
                        $pdf->Image($signaturePath, $imgX, $imgY, $imgW, $imgH, 'PNG');
                    }

                    // ── "Signed by" label ─────────────────────────────────────
                    $labelFontSize = max(4, min(7, $boxH * 0.18));
                    $pdf->SetFont('helvetica', '', $labelFontSize);
                    $pdf->SetTextColor(100, 100, 100);

                    $labelY = $boxY + $boxH * 0.65;
                    $pdf->SetXY($boxX + 2, $labelY);
                    $pdf->Cell($boxW - 4, $boxH * 0.16, 'Signed by: ' . $signerName, 0, 1, 'L');

                    // ── Date label ────────────────────────────────────────────
                    $pdf->SetXY($boxX + 2, $labelY + $boxH * 0.18);
                    $pdf->Cell($boxW - 4, $boxH * 0.16, 'Date: ' . $dateStr, 0, 1, 'L');
                }
            }

            $pdf->Output($outputPath, 'F');

        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Signing failed: ' . $e->getMessage());
        }

        $document->update([
            'admin_signed_at'  => now(),
            'status'           => 'approved',
            'signed_file_path' => $signedRelative,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Document signed successfully.');
    }
}