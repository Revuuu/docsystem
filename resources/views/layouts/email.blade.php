<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
">

<div style="
    max-width:600px;
    margin:40px auto;
    background:#ffffff;
    border-radius:12px;
    overflow:hidden;
    border:1px solid #e5e7eb;
">

    <div style="
        background:#16a34a;
        padding:24px;
        text-align:center;
    ">
        <img src="{{ $message->embed(public_path('images/phmc-logo.png')) }}"
             alt="PHMC"
             width="80">

        <h1 style="
            margin:12px 0 0;
            color:#ffffff;
            font-size:28px;
        ">
            DOCSYSTEM
        </h1>
    </div>

    <div style="padding:32px;">
        @yield('content')
    </div>

    <div style="
        background:#f9fafb;
        border-top:1px solid #e5e7eb;
        padding:16px;
        text-align:center;
        color:#9ca3af;
        font-size:12px;
    ">
        PHMC Document Management System
    </div>

</div>

</body>
</html>