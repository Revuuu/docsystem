<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Approval;

class DashboardController extends Controller
{
   public function index()
    {
        $user = auth()->user();
        $section = request()->query('section', 'upload');

        $approvals = collect();
        $pendingDocuments = collect();

        if ($user->role === 'approver') {

            $approvals = Approval::with('document.uploader')
                ->where('status', 'pending')
                ->where('user_id', $user->id) // ✅ IMPORTANT FIX
                ->latest()
                ->get();

            $pendingDocuments = $approvals->map(fn($a) => $a->document);
        }

        if ($user->role === 'requestor') {
            $pendingDocuments = Document::where('uploaded_by', $user->id)
                ->latest()
                ->get();
        }

        $signedDocuments = Document::whereNotNull('signed_file_path')
                ->whereNotNull('admin_signed_at')
                ->where('uploaded_by', $user->id)
                ->latest('admin_signed_at')
                ->get();

        $documents = Document::where('uploaded_by', $user->id)
                ->latest()
                ->get();

        return view('dashboard', compact(
            'approvals', 
            'pendingDocuments', 
            'signedDocuments', 
            'documents', 
            'section'
        ));
    }
}