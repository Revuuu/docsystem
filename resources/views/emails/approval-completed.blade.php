@extends('emails.layouts.app')

@section('content')

<h2 style="margin-top:0;color:#111827;">
    Document Fully Approved
</h2>

<p style="color:#4b5563;line-height:1.6;">
    Your document
    <strong>{{ $document->title }}</strong>
    has completed the approval process.
</p>

<div style="
    background:#ecfdf5;
    border:1px solid #bbf7d0;
    border-radius:8px;
    padding:16px;
">
    <strong>Approved By:</strong>

    <ul>
        @foreach($document->approvals->where('status', 'approved') as $approval)
            <li>{{ $approval->user->name }}</li>
        @endforeach
    </ul>
</div>

@endsection