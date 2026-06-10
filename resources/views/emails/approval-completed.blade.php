<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Document Fully Approved</h2>

<p>
Your document
<b>{{ $document->title }}</b>
has completed the approval process.
</p>

<p>Approved By:</p>

<ul>
@foreach($document->approvals()->where('status','approved')->get() as $approval)
    <li>{{ $approval->user->name }}</li>
@endforeach
</ul>
</body>
</html>