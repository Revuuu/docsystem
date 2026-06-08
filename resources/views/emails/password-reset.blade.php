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
            Password Reset Request
        </h2>

        <p style="
            color:#4b5563;
            line-height:1.7;
        ">
            We received a request to reset the password for your
            DOCSYSTEM account.
        </p>

        <p style="
            color:#4b5563;
            line-height:1.7;
        ">
            Click the button below to create a new password.
        </p>

        <div style="
            text-align:center;
            margin:35px 0;
        ">

            <a href="{{ $resetUrl }}"
               style="
                display:inline-block;
                background:#16a34a;
                color:#ffffff;
                text-decoration:none;
                padding:14px 30px;
                border-radius:8px;
                font-size:15px;
                font-weight:700;
            ">
                Reset Password
            </a>

        </div>

        <div style="
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:8px;
            padding:16px;
            margin-top:24px;
        ">

            <p style="
                margin:0;
                color:#6b7280;
                font-size:14px;
            ">
                This password reset link will expire in
                <strong>60 minutes</strong>.
            </p>

        </div>

        <p style="
            margin-top:24px;
            color:#dc2626;
            font-weight:600;
        ">
            If you did not request a password reset, no further action is required.
        </p>

        <hr style="
            border:none;
            border-top:1px solid #e5e7eb;
            margin:30px 0;
        ">

        <p style="
            color:#6b7280;
            font-size:13px;
            line-height:1.6;
        ">
            If the button above does not work, copy and paste the following URL into your browser:
        </p>

        <p style="
            word-break:break-all;
            font-size:12px;
            color:#374151;
        ">
            {{ $resetUrl }}
        </p>

        <p style="
            margin-top:30px;
            color:#6b7280;
            font-size:14px;
        ">
            Regards,<br>
            <strong>DOCSYSTEM Team</strong><br>
            Perpetual Help Medical Center - Las Piñas
        </p>

    </div>

</div>

</body>
</html>