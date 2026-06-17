<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Approval;
use App\Models\User;
use App\Models\AuditLog;

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

        $approvers = User::where('id', '!=', $user->id)
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
            ->orderBy('name')
            ->get();

        $documents = Document::with('approvals.user')
            ->where('uploaded_by', $user->id)
            ->latest()
            ->get();

        $signedDocuments = Document::where('status', 'approved')
        ->where('uploaded_by', $user->id)
        ->latest('updated_at')
        ->get();

        $approvals = Approval::with('document.uploader')
        ->where('user_id', $user->id)
        ->whereIn('status', [
            'pending',
            'waiting',
        ])
        ->latest()
        ->get();

        $pendingDocuments = $approvals->map(fn ($approval) => $approval->document);

        if ($userRole === 'admin') {
            $users = User::latest()->get();
            $allDocuments = Document::latest()->get();

            $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(10);

            $totalDocuments = Document::count();

            $pendingDocuments = Document::where('status', 'pending')->count();

            $approvedDocuments = Document::where('status', 'approved')->count();

            $rejectedDocuments = Document::where('status', 'rejected')->count();

            $totalUsers = User::count();

            $documentsThisMonth = Document::whereMonth(
                'created_at',
                now()->month
            )->count();

            $documentsToday = Document::whereDate(
                'created_at',
                today()
            )->count();

            $recentActivities = AuditLog::with('user')
                ->latest()
                ->take(10)
                ->get();
                
            return view('admin.index', compact(
                'users',
                'allDocuments',
                'documents',
                'signedDocuments',
                'approvals',
                'pendingDocuments',
                'approvers',
                'auditLogs',
                'section',

                'totalDocuments',
                'approvedDocuments',
                'rejectedDocuments',
                'totalUsers',
                'documentsThisMonth',
                'documentsToday',
                'recentActivities'
            ));
        }

        return view('dashboard.index', compact(
            'documents',
            'signedDocuments',
            'approvals',
            'pendingDocuments',
            'approvers',
            'section'
        ));
    }
}