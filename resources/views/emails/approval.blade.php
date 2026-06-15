@extends('emails.layouts.app')

@section('content')

<h2 style="margin-top:0;color:#111827;">
    Document Approval Required
</h2>

<p style="color:#4b5563;line-height:1.6;">
    Hello <strong>{{ $approval->user->name }}</strong>,
</p>

<p style="color:#4b5563;line-height:1.6;">
    A document is currently awaiting your review and approval.
</p>

<div style="
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-left:4px solid #16a34a;
    border-radius:8px;
    padding:16px;
    margin:24px 0;
">
    <strong>{{ $approval->document->title }}</strong>
</div>

@endsection