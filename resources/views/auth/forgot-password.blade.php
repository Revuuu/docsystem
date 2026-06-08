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

.branding-panel img{
    width:120px;
}

.branding-text h1{
    font-size:72px;
    font-weight:900;
    color:#16a34a;
}

.branding-text h2{
    font-size:22px;
    font-weight:700;
    color:#18181b;
}

.branding-panel p{
    max-width:600px;
    font-size:16px;
    line-height:1.8;
    color:#71717a;
}

.login-card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:40px;
    box-shadow:0 12px 40px rgba(15,23,42,.08);
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
    line-height:1.6;
}

.form-group{
    margin-bottom:18px;
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
    font-size:14px;
    outline:none;
}

.form-group input:focus{
    border-color:#16a34a;
    box-shadow:0 0 0 3px rgba(22,163,74,.14);
}

.reset-btn{
    width:100%;
    height:48px;

    border:none;
    border-radius:10px;

    background:#16a34a;
    color:#fff;

    font-size:14px;
    font-weight:700;
    cursor:pointer;
}

.reset-btn:hover{
    background:#15803d;
}

.back-login{
    margin-top:18px;
    text-align:center;
}

.back-login a{
    color:#16a34a;
    text-decoration:none;
}

.back-login a:hover{
    text-decoration:underline;
}

.status-message{
    background:#dcfce7;
    color:#166534;
    border:1px solid #bbf7d0;
    border-radius:10px;
    padding:12px;
    margin-bottom:18px;
}

.error-message{
    color:#dc2626;
    font-size:13px;
    margin-top:5px;
}

@media(max-width:768px){

    .login-layout{
        grid-template-columns:1fr;
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

}
</style>
<x-guest-layout>
    <div class="login-header">
        <h2>Forgot Password</h2>
    </div>

    @if(session('status'))
        <div class="status-message">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label>Email Address</label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder="Enter your email"
            >

            @error('email')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="reset-btn">
            SEND RESET LINK
        </button>

        <div class="back-login">
            <a href="{{ route('login') }}">
                Back to Login
            </a>
        </div>
    </form>

</x-guest-layout>