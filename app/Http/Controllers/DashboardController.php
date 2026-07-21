<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Approval;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\DocumentTemplate;

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

        $documentTemplates = DocumentTemplate::query()
                ->with([
                    'createdBy:id,name',
                    'updatedBy:id,name',
                ])
                ->orderBy('template_key')
                ->orderByDesc('version')
                ->get();

        if ($userRole === 'admin') {
            $search = request('search');
            $role = request('role');
            $status = request('status');
            $date = request('date');

            $users = User::query()

                ->when($search, function ($query) use ($search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    });

                })

                ->when($role, function ($query) use ($role) {

                    $query->where('role', $role);

                })

                ->when($status, function ($query) use ($status) {

                    if ($status === 'verified') {
                        $query->whereNotNull('email_verified_at');
                    }

                    if ($status === 'unverified') {
                        $query->whereNull('email_verified_at');
                    }

                })

                ->when($date, function ($query) use ($date) {

                    $query->whereDate(
                        'created_at',
                        $date
                    );

                })

                ->latest()

                ->get();

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
            
            $signatureTemplates = config(
                'signature_templates',
                []
            );
                
            return view('admin.index', compact(
                'users',
                'allDocuments',
                'documents',
                'signedDocuments',
                'approvals',
                'pendingDocuments',
                'documentTemplates',
                'signatureTemplates',
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
            'documentTemplates',
            'approvals',
            'pendingDocuments',
            'approvers',
            'section'
        ));
    }
}