<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DocumentMessageController;
use App\Http\Controllers\Admin\DocumentTemplateController;
use App\Http\Controllers\Admin\DocumentApprovalWorkflowController;
use App\Http\Controllers\Admin\TemporaryDocumentExtractionController;
use App\Http\Controllers\PurchaseOrderController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'otp'])->group(function () {

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

        Route::post('/users/{user}/reset-password',[UserController::class, 'resetPassword'])->name('users.resetPassword');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    
        Route::patch(
            '/users/{user}/unverify',
            [UserController::class, 'unverify']
        )->name('users.unverify');

        Route::put(
            '/users/{user}',
            [UserController::class, 'update']
        )->name('users.update');

        Route::post(
            '/document-templates',
            [DocumentTemplateController::class, 'store']
        )->name('document-templates.store');

        Route::patch(
            '/document-templates/{documentTemplate}/activate',
            [DocumentTemplateController::class, 'activate']
        )->name('document-templates.activate');

        Route::post(
            '/document-approval-workflows',
            [DocumentApprovalWorkflowController::class, 'store']
        )->name('document-approval-workflows.store');

        Route::post(
            '/admin/documents/temporary-po',
            [TemporaryDocumentExtractionController::class, 'store']
        )->name('admin.documents.temporary-po.store');
    });

    Route::get('/signature', [SignatureController::class, 'index'])
    ->name('signature.index');

    Route::post('/signature/upload', [SignatureController::class, 'upload'])
        ->name('signature.upload');

    Route::post('/signature/draw', [SignatureController::class, 'draw'])
        ->name('signature.draw');

    Route::delete('/signature/remove', [SignatureController::class, 'remove'])
    ->name('signature.remove');

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

    Route::get('/documents/{document}/messages', [DocumentMessageController::class, 'index'])
    ->name('documents.messages.index');

    Route::post('/documents/{document}/messages', [DocumentMessageController::class, 'store'])
        ->name('documents.messages.store');

    Route::post(
        '/admin/documents/temporary-po',
        [TemporaryDocumentExtractionController::class, 'store']
    )->name('admin.documents.temporary-po.store');
});

Route::get('/document-messages/{documentMessage}/attachment',[DocumentMessageController::class,'attachment',])
->name('document-messages.attachment');

Route::get('/document-messages/{documentMessage}/attachments/{attachmentIndex}',[DocumentMessageController::class, 'attachment']
)->whereNumber('attachmentIndex')->name('document-messages.attachments.show');

Route::get('/users/{user}/edit',
    [UserController::class, 'edit'])
    ->name('users.edit');

Route::post('/users/{user}/reset-password',
    [UserController::class, 'resetPassword'])
    ->name('users.resetPassword');

Route::get(
'/purchase-orders/{poNo}/index',
[PurchaseOrderController::class, 'show']
)
->whereNumber('poNo')
->name('purchase-orders.index');

Route::get('/test-mail', function () {

    Mail::raw(
        'DOCSYSTEM SMTP test.',
        function ($message) {

            $message->to('makarovdreyar28@gmail.com')
                    ->subject('DOCSYSTEM Mail Test');
        }
    );

    return 'Mail Sent';
});
require __DIR__.'/auth.php';