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
    $approvals = collect();
    $pendingDocuments = collect();

    if ($user->role === 'admin') {
        $pendingDocuments = Document::where('status', 'pending')
            ->whereNull('admin_signed_at')
            ->get();
    }

    if ($user->role === 'approver') {
        $approvals = Approval::with('document.uploader')
            ->where('status', 'pending')
            ->get();
    }

    return view('dashboard', compact('approvals', 'pendingDocuments'));
}
}