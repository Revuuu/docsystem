@extends('layouts.app')

@section('title', ucfirst(auth()->user()->roles->first()?->name ?? auth()->user()->role) . ' Dashboard')

@section('content')

@include('partials.upload-section')

@php
    use Illuminate\Pagination\LengthAwarePaginator;

    $search = strtolower(request('search', ''));
    $status = strtolower(request('status', ''));

    $assignedDocuments = $approvals
        ->pluck('document')
        ->filter();

    $allMyDocuments = $documents
        ->merge($assignedDocuments)
        ->unique('id')
        ->sortByDesc('created_at')
        ->values();

    $filteredDocuments = $allMyDocuments
        ->filter(function ($doc) use ($search, $status) {
            $latestVersion = $doc->latestVersion();

            $title = strtolower($doc->title ?? '');

            $file = strtolower(
                $latestVersion
                    ? basename($latestVersion->generated_storage_path)
                    : ''
            );

            $myApproval = $doc->approvals
                ->where('user_id', auth()->id())
                ->sortBy('step_order')
                ->first();

            $docStatus = strtolower(
                $myApproval?->status ?? $doc->status
            );

            return (
                empty($search) ||
                str_contains($title, $search) ||
                str_contains($file, $search)
            ) && (
                empty($status) ||
                $docStatus === $status
            );
        })
        ->values();

    $perPage = 10;

    $currentPage = LengthAwarePaginator::resolveCurrentPage();

    $currentItems = $filteredDocuments
        ->slice(($currentPage - 1) * $perPage, $perPage)
        ->values();

    $myDocuments = new LengthAwarePaginator(
        $currentItems,
        $allMyDocuments->count(),
        $perPage,
        $currentPage,
        [
            'path' => request()->url(),
            'query' => request()->query(),
        ]
    );

    $filteredDocumentCount = $filteredDocuments->count();
@endphp

<div id="section-dashboard" class="dashboard-section">
    @include('dashboard.user')
</div>

@include('dashboard.sections.documents')

@include('dashboard.sections.profile')

<x-chat-modal />

<x-messenger-widget :documents="$myDocuments" />
<div id="section-messenger" class="dashboard-section">
    <x-messenger-panel :documents="$myDocuments" />
</div>

<x-signature-modals />

<x-document-action-modals />

@include('partials.password-modal')

<script>
    window.authUserId = {{ auth()->id() }};
    window.authUserName = @json(auth()->user()->name);

    function openDocumentChat(documentId, title) {
        Chat.open(documentId, title);
    }

    function closeDocumentChat() {
        Chat.close();
    }
</script>

@endsection