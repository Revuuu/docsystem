<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>

<h2>Document Approval Required</h2>

<p>Hello {{ $approval->user->name }},</p>

<p>
A document is waiting for your approval.
</p>

<p>
<b>Document:</b>
{{ $approval->document->title }}
</p>

<p>
Please log in to the system and review the document.
</p>

<p>
    <a href="{{ route('dashboard') }}"
       style="
           background:#0d6efd;
           border:1px solid #0d6efd;
           border-radius:6px;
           color:#ffffff !important;
           display:inline-block;
           font-family:Arial, sans-serif;
           font-size:14px;
           font-weight:bold;
           line-height:44px;
           text-align:center;
           text-decoration:none;
           width:200px;
           -webkit-text-size-adjust:none;
       ">
        Review Document
    </a>
</p>
</body>
</html>