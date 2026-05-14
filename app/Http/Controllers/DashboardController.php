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

        $userRole = $user->roles->first()?->name ?? $user->role;

        $hierarchy = [
            'staff' => 1,
            'supervisor' => 2,
            'depthead' => 3,
            'division' => 4,
            'executive' => 5,
            'admin' => 6,
        ];

        if (! isset($hierarchy[$userRole])) {
            abort(403, 'Unauthorized role.');
        }

        $allowedRoles = collect($hierarchy)
            ->filter(fn ($level) => $level > $hierarchy[$userRole])
            ->keys()
            ->toArray();

        $approvers = User::whereIn('role', $allowedRoles)
            ->orderByRaw("
                CASE role
                    WHEN 'staff' THEN 1
                    WHEN 'supervisor' THEN 2
                    WHEN 'depthead' THEN 3
                    WHEN 'division' THEN 4
                    WHEN 'executive' THEN 5
                    WHEN 'admin' THEN 6
                END
            ")
            ->get();

        $documents = Document::where('uploaded_by', $user->id)
            ->latest()
            ->get();

        $signedDocuments = Document::whereNotNull('signed_file_path')
            ->where('uploaded_by', $user->id)
            ->latest('updated_at')
            ->get();

        $approvals = Approval::with('document.uploader')
            ->where('status', 'pending')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $pendingDocuments = $approvals->map(fn ($approval) => $approval->document);

        if ($userRole === 'admin') {
            $users = User::latest()->get();
            $allDocuments = Document::latest()->get();

            return view('admin.index', compact(
                'users',
                'allDocuments',
                'documents',
                'signedDocuments',
                'approvals',
                'pendingDocuments',
                'approvers',
                'section'
            ));
        }

        return view($userRole . '.index', compact(
            'documents',
            'signedDocuments',
            'approvals',
            'pendingDocuments',
            'approvers',
            'section'
        ));
    }
}