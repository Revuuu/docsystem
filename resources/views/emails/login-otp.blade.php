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

        <h2 style="
            margin-top:0;
            color:#111827;
        ">
            Login Verification
        </h2>

        <p style="
            color:#4b5563;
            line-height:1.6;
        ">
            A login attempt was made using your account.
            Please use the verification code below to continue.
        </p>

        <div style="
            text-align:center;
            margin:30px 0;
        ">

            <span style="
                display:inline-block;
                font-size:36px;
                font-weight:700;
                letter-spacing:8px;
                color:#16a34a;
                padding:16px 24px;
                border:2px dashed #16a34a;
                border-radius:10px;
            ">
                {{ $otp }}
            </span>

        </div>

        <p style="
            color:#dc2626;
            font-weight:600;
        ">
            This code expires in 3 minutes.
        </p>

        <p style="
            color:#6b7280;
            font-size:14px;
            margin-top:24px;
        ">
            If you did not attempt to sign in,
            you may safely ignore this email.
        </p>

    </div>

</div>

</body>
</html>