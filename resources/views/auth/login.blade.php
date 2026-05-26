<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DOCSYSTEM Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

    <style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family: Arial, sans-serif;
        background:#e9ddc8;
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        overflow:hidden;
    }

    .page-wrapper{
        width:100%;
        display:flex;
        flex-direction:column;
        align-items:center;
    }

    .dashboard-title{
        font-size:32px;
        font-weight:800;
        margin-bottom:20px;
        color:#1A6C35;
    }

    .page-wrapper{
        position:relative;
        width:420px;
        background:#f7f3ea;
        border-radius:16px;
        padding:40px 35px;
        box-shadow:0 8px 20px rgba(0,0,0,0.15);
        overflow:hidden;
    }

    /* Decorative Shapes */
    .login-card::before{
        content:'';
        position:absolute;
        top:-40px;
        right:-40px;
        width:140px;
        height:140px;
        background:#c9a24b;
        border-radius:50%;
    }

    .login-card::after{
        content:'';
        position:absolute;
        bottom:-60px;
        left:-60px;
        width:180px;
        height:180px;
        background:#c9a24b;
        border-radius:50%;
    }

    .logo-container {
    display: flex;
    align-items: center;
    margin-bottom: 50px;
    }

    .logo-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
    }

    .logo-text h1 {
        font-size: 100px;
        margin: 0;
        line-height: 1;
    }

    .logo-text h2 {
        font-size: 19px;
        margin: 0;
        line-height: 1;
    }

    .logo-container img{
        width:100px;
    }

    .form-group{
        margin-bottom:18px;
        position:relative;
        z-index:2;
    }

    .form-group label{
        display:block;
        margin-bottom:6px;
        font-size:14px;
        font-weight:600;
        color:#222;
    }

    .form-group input{
        width:100%;
        padding:12px 14px;
        border:1px solid #bbb;
        border-radius:8px;
        font-size:14px;
        outline:none;
        background:#fff;
    }

    .form-group input:focus{
        border-color:#0c7a3d;
    }

    .login-btn{
        width:100%;
        background:#0c7a3d;
        color:#fff;
        border:none;
        padding:12px;
        border-radius:8px;
        font-weight:700;
        cursor:pointer;
        transition:.2s;
        position:relative;
        z-index:2;
    }

    .login-btn:hover{
        background:#095e2f;
    }

    .forgot-password{
        text-align:center;
        margin-top:15px;
        position:relative;
        z-index:2;
    }

    .forgot-password a{
        text-decoration:none;
        color:#2d6b45;
        font-size:14px;
    }

    .password-toggle-btn {
    position: absolute;
    right: 12px;
    bottom: 10px;
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    }

    /* =========================
   LARGE DESKTOP
========================= */
@media (min-width: 1400px) {

    .login-card{
        width:500px;
    }

    .logo-text h1{
        font-size:110px;
    }

    .logo-text h2{
        font-size:22px;
    }
}


/* =========================
   LAPTOP
========================= */
@media (max-width: 1200px){

    .login-card{
        width:430px;
    }

    .logo-text h1{
        font-size:90px;
    }

    .logo-text h2{
        font-size:18px;
    }
}


/* =========================
   TABLET
========================= */
@media (max-width: 768px){

    body{
        padding:20px;
        overflow:auto;
    }

    .login-card{
        width:100%;
        max-width:420px;
        padding:35px 25px;
    }

    .logo-container{
        flex-direction:column;
        justify-content:center;
        text-align:center;
        margin-bottom:35px;
    }

    .logo-container img{
        width:80px;
        margin-bottom:10px;
    }

    .logo-text h1{
        font-size:70px;
    }

    .logo-text h2{
        font-size:16px;
    }
}


/* =========================
   MOBILE
========================= */
@media (max-width: 500px){

    .login-card{
        padding:25px 18px;
        border-radius:12px;
    }

    .logo-container img{
        width:70px;
    }

    .logo-text h1{
        font-size:52px;
    }

    .logo-text h2{
        font-size:13px;
    }

    .form-group input{
        padding:11px 12px;
        font-size:14px;
    }

    .login-btn{
        padding:11px;
        font-size:14px;
    }
}


/* =========================
   SMALL MOBILE
========================= */
@media (max-width: 360px){

    .logo-text h1{
        font-size:42px;
    }

    .logo-text h2{
        font-size:11px;
    }

    .login-card{
        padding:20px 15px;
    }
}
</style>
<script>
    function togglePasswordVisibility() {
    const input = document.getElementById('loginPassword');
    const eyeIcon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        // Change eye to "slashed" version
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />`;
    } else {
        input.type = 'password';
        // Revert back to normal eye
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
    }
}
</script>
</head>
<body>
<div class="login-card">

        <div class="logo-container">
            <img src="{{ asset('images/phmc-logo.png') }}" alt="PHMC Logo">
           <div class="logo-text">
                <h1 class="dashboard-title">PHMC</h1>
                <h2 class="dashboard-title">MEDICAL CENTER - LAS PIÑAS</h2>
            </div>
        </div>
        

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>User ID</label>
                <input type="text"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="User ID"
                       required
                       autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password"
                       id="loginPassword"
                       name="password"
                       placeholder="Password"
                       required>
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Toggle password visibility">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b7280" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>

            <button type="submit" class="login-btn">
                SIGN IN
            </button>

            <div class="forgot-password">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        Forgot password
                    </a>
                @endif
            </div>

        </form>


</div>

</body>
</html>