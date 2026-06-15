@extends('emails.layouts.app')

@section('content')

<h2 style="margin-top:0;color:#111827;">
    Document Approval Progress
</h2>

<p style="color:#4b5563;line-height:1.6;">
    Your document
    <strong>{{ $approval->document->title }}</strong>
    has been approved by
    <strong>{{ $approval->user->name }}</strong>.
</p>

<div style="
    background:#ecfdf5;
    border:1px solid #bbf7d0;
    border-radius:8px;
    padding:16px;
">
    Current Progress:
    {{ $approval->document->approvals()->where('status','approved')->count() }}
    /
    {{ $approval->document->approvals()->count() }}
</div>

@endsection