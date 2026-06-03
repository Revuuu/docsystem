<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
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

        $hierarchy = [
            'staff' => 1,
            'supervisor' => 2,
            'depthead' => 3,
            'division' => 4,
            'executive' => 5,
            'admin' => 6,
        ];

        $uploader = Auth::user();
        $uploaderRole = $uploader->roles->first()?->name ?? $uploader->role;

        $approvers = User::whereIn('id', $request->approvers)
            ->where('id', '!=', Auth::id())
            ->get()
            ->sortBy(function ($approver) use ($hierarchy) {

                $approverRole = $approver->roles->first()?->name
                    ?? $approver->role;

                return $hierarchy[$approverRole] ?? 999;
            })
            ->values();

        if ($approvers->isEmpty()) {

            return redirect()
                ->route('dashboard')
                ->with('document_error', 'Please select at least one valid approver.')
                ->with('section', 'documents');
        }

       $uploadedFile = $request->file('file');

        $document = Document::create([
            'title' => $request->title,
            'uploaded_by' => Auth::id(),
            'approver_id' => $approvers->first()->id,
            'status' => 'pending',
        ]);

        $storageFileName =
    'document_' .
    $document->id .
    '_v1.pdf';

$storagePath =
    'document/' .
    $storageFileName;

Storage::disk('local')->put(
    $storagePath,
    file_get_contents(
        $uploadedFile->getRealPath()
    )
);

        $document->files()->create([

            'file_name' => $uploadedFile->getClientOriginalName(),

            'file_path' => $storagePath,

            'mime_type' => $uploadedFile->getMimeType(),

            'file_size' => $uploadedFile->getSize(),

            'version' => 1,

            'is_signed' => false,

            'is_current' => true,

            'uploaded_by' => auth()->id(),

        ]);


        foreach ($approvers as $index => $approver) {
            Approval::create([
                'document_id' => $document->id,
                'user_id' => $approver->id,
                'step_order' => $index + 1,
                'status' => $index === 0 ? 'pending' : 'waiting',
                'received_at' => $index === 0 ? now() : null,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('document_success', 'Document uploaded successfully!')
            ->with('section', 'documents');
    }
}