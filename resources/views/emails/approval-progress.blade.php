<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Document Approval Progress</h2>

<p>
Your document
<b>{{ $approval->document->title }}</b>
has been approved by
<b>{{ $approval->user->name }}</b>.
</p>

<p>
Current Progress:
{{ $approval->document->approvals()->where('status','approved')->count() }}
/
{{ $approval->document->approvals()->count() }}
approvals completed.
</p>
</body>
</html>