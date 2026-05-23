<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::post('/documents', [DocumentController::class, 'store'])
        ->name('documents.store');

    Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');

    Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])
    ->name('approvals.reject');

    Route::post('/documents/{document}/admin-sign', [DocumentController::class, 'adminSign'])
        ->name('documents.adminSign');

    Route::middleware(['role:admin'])->group(function () {
        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
            ->name('users.updateRole');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    });

    Route::get('/signature', [SignatureController::class, 'index'])
    ->name('signature.index');

    Route::post('/signature/upload', [SignatureController::class, 'upload'])
        ->name('signature.upload');

    Route::post('/signature/draw', [SignatureController::class, 'draw'])
        ->name('signature.draw');

    Route::delete('/signature/remove', [SignatureController::class, 'remove'])
    ->name('signature.remove');

    Route::get('/preview-signature', function () {
    $user = auth()->user();

    $file = Storage::disk('private')->get($user->signature_path);
    $base64 = base64_encode($file);

    return '<img src="data:image/png;base64,' . $base64 . '">';
});

    Route::post('/verify-password', function (\Illuminate\Http\Request $request) {

    if (!\Illuminate\Support\Facades\Hash::check(
        $request->password,
        auth()->user()->password
    )) {

        return response()->json([
            'success' => false,
            'message' => 'Incorrect account password.'
        ], 422);
    }

    return response()->json([
        'success' => true
    ]);
});

Route::get(
    '/files/view/{id}',
    [FileController::class, 'view']
)->name('files.view');

Route::get(
    '/files/download/{id}',
    [FileController::class, 'download']
)->name('files.download');

Route::get(
    '/signatures/view/{id}',
    [FileController::class, 'signature']
)->name('signatures.view');
});

require __DIR__.'/auth.php';