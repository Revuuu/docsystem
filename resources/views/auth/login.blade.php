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
    font-family: Inter, Arial, sans-serif;

    background:
        radial-gradient(
            circle at top left,
            rgba(22,163,74,.06),
            transparent 40%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(201,162,75,.08),
            transparent 40%
        ),
        #fafafa;

    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    overflow:auto;
}
.login-layout{
    width:100%;
    max-width:1100px;

    display:grid;
    grid-template-columns:1fr 480px;

    align-items:center;
    gap:60px;
}
.branding-header{
    display:flex;
    align-items:center;
    gap:24px;

    margin-bottom:24px;
}

.branding-panel{
    animation:fadeInLeft .8s ease;
}

.branding-panel img{
    width:120px;
    flex-shrink:0;

    animation:floatLogo 5s ease-in-out infinite;
}
.branding-text{
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.branding-text h1{
    font-size:72px;
    font-weight:900;
    line-height:1;

    color:#16a34a;

    margin:0 0 8px;
}
.branding-text h2{
    font-size:22px;
    font-weight:700;

    color:#18181b;

    margin:0;
}

.branding-panel p{
    max-width:600px;

    font-size:16px;
    line-height:1.8;

    color:#71717a;
}

.login-header{
    margin-bottom:28px;
}

.login-header h2{
    font-size:32px;
    font-weight:800;
    color:#18181b;
    margin-bottom:6px;
}

.login-header p{
    color:#71717a;
}
.login-card{
    position:relative;
    width:100%;
    max-width:480px;

    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;

    padding:40px;

    box-shadow:
        0 12px 40px rgba(15,23,42,.08);

    animation:fadeUp .8s ease;

    transition:.3s ease;
}

.login-card:hover{
    transform:translateY(-4px);

    box-shadow:
        0 20px 50px rgba(15,23,42,.12);
}
.logo-container{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:14px;

    margin-bottom:35px;

    animation:fadeIn .8s ease;
}

.logo-container img{
    width:85px;
    flex-shrink:0;
}

.logo-text{
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.logo-text h1{
    margin:0;
    line-height:1;

    font-size:72px;
    font-weight:900;

    color:#16a34a;
}

.logo-text h2{
    margin:0;

    font-size:16px;
    font-weight:700;

    color:#71717a;

    letter-spacing:.5px;
}

.dashboard-title{
    margin:0;
}

.form-group{
    position:relative;
    margin-bottom:18px;

    opacity:0;

    animation:fadeUp .6s ease forwards;
}

.form-group:nth-child(1){
    animation-delay:.15s;
}

.form-group:nth-child(2){
    animation-delay:.30s;
}
.form-group label{
    display:block;

    margin-bottom:8px;

    color:#27272a;

    font-size:12px;
    font-weight:700;

    text-transform:uppercase;
    letter-spacing:.05em;
}

.form-group input{
    width:100%;
    height:46px;

    padding:0 14px;

    border:1px solid #e5e7eb;
    border-radius:10px;

    background:#fff;

    font-size:14px;

    transition:all .2s ease;

    outline:none;
}

.form-group input:focus{
    border-color:#16a34a;

    box-shadow:
        0 0 0 3px rgba(22,163,74,.14);
}

.password-toggle-btn{
    position:absolute;

    right:14px;
    bottom:13px;

    background:none;
    border:none;

    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;
}

.login-error-box{
    margin:-4px 0 14px;
}

.login-error-box p{
    color:#dc2626;

    font-size:12px;
    font-weight:500;

    margin-bottom:3px;
}

.login-btn{
    width:100%;
    height:48px;

    border:none;
    border-radius:10px;

    background:#16a34a;
    color:#fff;

    font-size:14px;
    font-weight:700;
    letter-spacing:.5px;

    cursor:pointer;

    position:relative;
    overflow:hidden;

    transition:all .3s ease;
}

.login-btn:hover{
    background:#15803d;

    transform:translateY(-2px);

    box-shadow:
        0 8px 24px rgba(22,163,74,.25);
}
.login-btn::before{
    content:'';

    position:absolute;
    top:0;
    left:-120%;

    width:100%;
    height:100%;

    background:
        linear-gradient(
            120deg,
            transparent,
            rgba(255,255,255,.25),
            transparent
        );

    transition:.8s;
}

.login-btn:hover::before{
    left:120%;
}
.forgot-password{
    margin-top:16px;
    text-align:center;
}

.forgot-password a{
    color:#16a34a;

    text-decoration:none;

    font-size:14px;
    font-weight:500;
}

.forgot-password a:hover{
    text-decoration:underline;
}

@keyframes floatLogo{

    0%,100%{
        transform:translateY(0);
    }

    50%{
        transform:translateY(-8px);
    }
}

@keyframes fadeInLeft{

    from{
        opacity:0;
        transform:translateX(-30px);
    }

    to{
        opacity:1;
        transform:translateX(0);
    }
}

@keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(25px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* Tablet */

@media(max-width:768px){
  .login-layout{
        grid-template-columns:1fr;
        gap:40px;
    }

    .branding-header{
        flex-direction:column;
        text-align:center;
    }

    .branding-text h1{
        font-size:48px;
    }

    .branding-text h2{
        font-size:18px;
    }

    .branding-panel{
        text-align:center;
    }

    .branding-panel p{
        margin:auto;
    }
}

/* Mobile */

@media(max-width:480px){

    .login-card{
        padding:24px 18px;
        border-radius:14px;
    }

    .logo-container img{
        width:65px;
    }

    .logo-text h1{
        font-size:42px;
    }

    .logo-text h2{
        font-size:12px;
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

<div class="login-layout">

    <div class="branding-panel">

    <div class="branding-header">

        <img src="{{ asset('images/phmc-logo.png') }}"
             alt="PHMC Logo">

        <div class="branding-text">

            <h1>DOCSYSTEM</h1>

            <h2>Perpetual Help Medical Center - Las Piñas</h2>

        </div>

    </div>

    <p>
        Document Workflow Management System
        for routing, approval, tracking,
        and secure document signing.
    </p>

</div>

    <div class="login-card">

        <div class="login-header">

            <h2>Welcome Back</h2>

            <p>
                Sign in to continue
            </p>

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
            @if ($errors->any())
                <div class="login-error-box">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
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
</div>

</body>
</html>