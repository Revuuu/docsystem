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
    <h1 style="
        margin:0;
        color:#ffffff;
    ">
        DOCSYSTEM
    </h1>
</div>

<div style="padding:32px;">

    <h2>
        Welcome to DOCSYSTEM
    </h2>

    <p>
        Your account has been created by the system administrator.
    </p>

    <p>
        To activate your account, please click the button below and create your password.
    </p>

    <div style="text-align:center;margin:30px 0;">

        <a href="{{ $setupUrl }}"
           style="
                background:#16a34a;
                color:#ffffff;
                padding:14px 24px;
                text-decoration:none;
                border-radius:8px;
                font-weight:bold;
           ">
            Create Password
        </a>

    </div>

    <p>
        Once your password is created, your account will be activated and you can sign in to DOCSYSTEM.
    </p>

    <p>
        For security purposes, an OTP verification code will be required every time you log in.
    </p>

</div>

</div>

</body>
</html>
