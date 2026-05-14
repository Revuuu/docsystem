<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Approval;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $section = request()->query('section', 'upload');

        if ($user->role === 'staff') {
            $documents = Document::where('uploaded_by', $user->id)
                ->latest()
                ->get();

            $signedDocuments = Document::whereNotNull('signed_file_path')
                ->whereNotNull('admin_signed_at')
                ->where('uploaded_by', $user->id)
                ->latest('admin_signed_at')
                ->get();

            $approvers = User::whereIn('role', [
                'supervisor',
                'depthead',
                'division',
                'executive',
            ])->get();

            return view('staff.index', compact(
                'documents',
                'signedDocuments',
                'approvers',
                'section'
            ));
        }

        if (in_array($user->role, ['supervisor', 'depthead', 'division', 'executive'])) {
            $approvals = Approval::with('document.uploader')
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $pendingDocuments = $approvals->map(fn ($approval) => $approval->document);

            return view($user->role . '.index', compact(
                'approvals',
                'pendingDocuments',
                'section'
            ));
        }

        if ($user->role === 'admin') {
            $documents = Document::latest()->get();
            $users = User::latest()->get();

            return view('admin.index', compact(
                'documents',
                'users',
                'section'
            ));
        }

        abort(403, 'Unauthorized role.');
    }
}