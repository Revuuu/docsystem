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
        'file'  => 'required|mimes:pdf|max:10240', // 10MB
    ]);

    // Save file
    $path = $request->file('file')->store('documents', 'public');

    // Save document
    $document = Document::create([
        'title'       => $request->title,
        'file_path'   => $path,
        'uploaded_by' => Auth::id(), // store user id, not name
        'status'      => 'pending',
    ]);

    // Create approval records for all approvers
    $approverIds = User::where('role', 'approver')->pluck('id');
    foreach ($approverIds as $userId) {
        Approval::create([
            'document_id' => $document->id,
            'user_id'     => $userId,
            'status'      => 'pending',
        ]);
    }

    return redirect()->route('dashboard')->with('success', 'Document uploaded successfully!');
}

    /**
     * Admin signs document
     */
    public function adminSign(Document $document)
{
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    if ($document->admin_signed_at !== null) {
        return redirect()->route('dashboard')
            ->with('error', 'Document already signed.');
    }

    $inputPath     = storage_path('app/public/' . $document->file_path);
    $signedName    = 'signed_' . basename($document->file_path);
    $signedRelative = 'documents/signed/' . $signedName;
    $outputPath    = storage_path('app/public/documents/signed/' . $signedName);
    $signaturePath = storage_path('app/public/signature.png');
    $signerName    = Auth::user()->name;
    $dateStr       = now()->format('F d, Y h:i A');

    // Create output directory if needed
    if (!file_exists(dirname($outputPath))) {
        mkdir(dirname($outputPath), 0755, true);
    }

    try {
        // Load existing PDF with FPDI
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($inputPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);

            $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // Stamp signature only on last page
            if ($i === $pageCount) {
                $boxX = $size['width'] - 75;
                $boxY = $size['height'] - 35;
                $boxW = 70;
                $boxH = 28;

                // Draw border box
                $pdf->SetDrawColor(180, 180, 180);
                $pdf->SetLineWidth(0.3);
                $pdf->Rect($boxX, $boxY, $boxW, $boxH);

                // Signature image — prominent, no black background
                if (file_exists($signaturePath)) {
                    $pdf->Image($signaturePath, $boxX + 2, $boxY + 2, 60, 15, 'PNG');
                }

                // Signed by name
                $pdf->SetFont('helvetica', '', 5);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetXY($boxX + 2, $boxY + 18);
                $pdf->Cell($boxW - 4, 3, 'Signed by: ' . $signerName, 0, 1, 'L');

                // Date
                $pdf->SetXY($boxX + 2, $boxY + 21);
                $pdf->Cell($boxW - 4, 3, 'Date: ' . $dateStr, 0, 1, 'L');
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