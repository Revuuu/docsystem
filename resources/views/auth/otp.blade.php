<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>DOCSYSTEM OTP Verification</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Inter,sans-serif;

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
}

.otp-card{
    width:100%;
    max-width:500px;

    background:#fff;

    border:1px solid #e5e7eb;

    border-radius:20px;

    padding:40px;

    box-shadow:
        0 12px 40px rgba(15,23,42,.08);
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    width:90px;
    margin-bottom:12px;
}

.logo h1{
    color:#16a34a;
    font-size:42px;
    font-weight:900;
}

.logo p{
    color:#6b7280;
    font-size:14px;
    margin-top:8px;
}

.icon-circle{
    width:72px;
    height:72px;

    margin:0 auto 20px;

    background:#dcfce7;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;
}

.icon-circle i{
    font-size:32px;
    color:#16a34a;
}

.title{
    text-align:center;
    margin-bottom:10px;

    font-size:28px;
    font-weight:800;
    color:#18181b;
}

.subtitle{
    text-align:center;
    color:#6b7280;
    line-height:1.6;
    margin-bottom:30px;
}

.email{
    color:#16a34a;
    font-weight:700;
}

.otp-container{
    display:flex;
    justify-content:center;
    gap:10px;
    margin-bottom:25px;
}

.otp-input{
    width:55px;
    height:60px;

    text-align:center;

    font-size:24px;
    font-weight:700;

    border:1px solid #d1d5db;
    border-radius:10px;

    outline:none;

    transition:.2s;
}

.otp-input:focus{
    border-color:#16a34a;

    box-shadow:
        0 0 0 4px rgba(22,163,74,.15);
}

.verify-btn{
    width:100%;
    height:48px;

    border:none;
    border-radius:10px;

    background:#16a34a;
    color:white;

    font-size:14px;
    font-weight:700;

    cursor:pointer;

    transition:.2s;
}

.verify-btn:hover{
    background:#15803d;
}

.resend-wrapper{
    margin-top:20px;
    text-align:center;
}

.resend-btn{
    border:none;
    background:none;

    color:#16a34a;

    cursor:pointer;

    font-weight:600;
}

.resend-btn:disabled{
    color:#9ca3af;
    cursor:not-allowed;
}

.timer{
    margin-top:8px;
    color:#6b7280;
    font-size:13px;
}

.error-box{
    background:#fef2f2;
    border:1px solid #fecaca;
    color:#dc2626;

    padding:12px;
    border-radius:10px;

    margin-bottom:20px;
}

.success-box{
    background:#f0fdf4;
    border:1px solid #bbf7d0;
    color:#15803d;

    padding:12px;
    border-radius:10px;

    margin-bottom:20px;
}

@media(max-width:480px){

    .otp-card{
        padding:24px;
    }

    .otp-input{
        width:42px;
        height:52px;
        font-size:20px;
    }

    .otp-container{
        gap:6px;
    }
}

</style>
</head>
<body>

<div class="otp-card">

    <div class="logo">

        <img src="{{ asset('images/phmc-logo.png') }}"
             alt="PHMC Logo">

        <h1>DOCSYSTEM</h1>

        <p>
            Perpetual Help Medical Center - Las Piñas
        </p>

    </div>

    <div class="icon-circle">
        <i class="bi bi-shield-lock-fill"></i>
    </div>

    <h2 class="title">
        Verify Login
    </h2>

    <p class="subtitle">
        A 6-digit verification code has been sent to
        <br>
        <span class="email">
            {{ auth()->user()->email }}
        </span>
    </p>

    @if ($errors->any())
        <div class="error-box">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="success-box">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('otp.verify') }}"
          id="otpForm">

        @csrf

        <div class="otp-container">

            <input type="text" maxlength="1" class="otp-input">
            <input type="text" maxlength="1" class="otp-input">
            <input type="text" maxlength="1" class="otp-input">
            <input type="text" maxlength="1" class="otp-input">
            <input type="text" maxlength="1" class="otp-input">
            <input type="text" maxlength="1" class="otp-input">

        </div>

        <input type="hidden"
               name="otp"
               id="otp">

        <button type="submit"
                class="verify-btn">

            VERIFY CODE

        </button>

    </form>

    <div class="resend-wrapper">

        <form method="POST"
              action="{{ route('otp.resend') }}">

            @csrf

            <button type="submit"
                    id="resendBtn"
                    class="resend-btn"
                    disabled>

                Resend OTP

            </button>

        </form>

        <div class="timer">
            Resend available in
            <span id="countdown">30</span>s
        </div>

    </div>

</div>

<script>

const inputs =
    document.querySelectorAll('.otp-input');

const hiddenInput =
    document.getElementById('otp');

inputs.forEach((input,index)=>{

    input.addEventListener('input',e=>{

        e.target.value =
            e.target.value.replace(/\D/g,'');

        if(
            e.target.value &&
            index < inputs.length - 1
        ){
            inputs[index+1].focus();
        }

        updateOtp();
    });

    input.addEventListener('keydown',e=>{

        if(
            e.key === 'Backspace' &&
            !e.target.value &&
            index > 0
        ){
            inputs[index-1].focus();
        }
    });

});

function updateOtp(){

    let otp='';

    inputs.forEach(input=>{
        otp += input.value;
    });

    hiddenInput.value = otp;

    if(otp.length === 6){
    document.querySelector('.verify-btn').focus();
}
}

inputs[0].focus();

let seconds = 30;

const countdown =
    document.getElementById('countdown');

const resendBtn =
    document.getElementById('resendBtn');

const timer =
    setInterval(()=>{

        seconds--;

        countdown.textContent =
            seconds;

        if(seconds <= 0){

            clearInterval(timer);

            resendBtn.disabled =
                false;

            countdown.textContent =
                0;
        }

    },1000);

document.addEventListener('paste',e=>{

    const data =
        e.clipboardData
        .getData('text')
        .replace(/\D/g,'');

    if(data.length === 6){

        inputs.forEach(
            (input,index)=>{
                input.value =
                    data[index];
            }
        );

        updateOtp();
    }

});

</script>

</body>
</html>