<?php

namespace App\Http\Controllers;

use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    public function view($id)
    {
        $id = decrypt($id);

        $file = DocumentFile::findOrFail($id);

        $path = storage_path(
            'app/private/' .
            $file->generated_storage_path
        );

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file(
            $path,
            [
                'Content-Type' => 'application/pdf'
            ]
        );
    }

    public function download($id)
    {
        $id = decrypt($id);

        $file = DocumentFile::findOrFail($id);

        $path = storage_path(
            'app/private/' .
            $file->generated_storage_path
        );

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download(
            $path,
            $file->generated_file_name
        );
    }

    public function signature($id)
    {
        $id = decrypt($id);

        $user = User::findOrFail($id);

        if (!$user->signature_path) {
            abort(404);
        }

        $path = storage_path(
            'app/private/' .
            $user->signature_path
        );

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}