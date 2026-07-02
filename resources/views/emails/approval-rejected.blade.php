@extends('layouts.email')

@section('content')

<h2 style="margin-top:0;color:#111827;">
    Document Rejected
</h2>

<p style="color:#4b5563;line-height:1.6;">
    Your document
    <strong>{{ $document->title }}</strong>
    has been rejected.
</p>

<div style="
    background:#fee2e2;
    border:1px solid #fecaca;
    border-radius:8px;
    padding:16px;
">
    <strong>Rejected By:</strong>

    <ul>
        @foreach($document->approvals->where('status', 'rejected') as $approval)
            <li>{{ $approval->user->name }}</li>
        @endforeach
    </ul>

    <strong>Remarks:</strong>

    <ul>
        @foreach($document->approvals->where('status', 'rejected') as $approval)
            <li>{{ $approval->remarks }}</li>
        @endforeach
    </ul>
</div>

@endsection