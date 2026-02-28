<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DOCSYSTEM Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;700;900&family=DM+Mono:wght@300;400;500&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:       #0d1117;
            --ink-mid:   #1c2333;
            --ink-soft:  #2d3a52;
            --paper:     #f5f0e8;
            --paper-warm:#ede8dc;
            --paper-mid: #d4cfc3;
            --gold:      #c9a84c;
            --gold-light:#e8c97a;
            --red-acc:   #b83232;
            --blue-acc:  #2a4a7f;
            --text-body: #1c2333;
            --text-muted:#6b7890;
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body:    'Libre Baskerville', Georgia, serif;
            --font-mono:    'DM Mono', monospace;
        }

        body {
            background-color: var(--paper);
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 3rem;
            max-width: 1200px;
            width: 100%;
            padding: 3rem;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-eyebrow {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .hero-eyebrow-line { width: 40px; height: 2px; background: var(--gold); }
        .hero-eyebrow-text {
            font-family: var(--font-mono);
            font-size: 0.68rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 5vw, 4rem);
            font-weight: 900;
            color: var(--ink);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .login-form {
            background: var(--paper-warm);
            padding: 2.5rem;
            border: 1px solid var(--paper-mid);
            box-shadow: 0 8px 24px rgba(13,17,23,0.12);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .login-form h2 {
            font-family: var(--font-display);
            font-size: 1.8rem;
            color: var(--ink);
            margin-bottom: 1rem;
            text-align: center;
        }

        .login-form label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-body);
            margin-bottom: 0.25rem;
        }

        /* Uniform and clickable email/password inputs */
        .login-form input[type="email"],
        .login-form input[type="password"] {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1.5px solid var(--paper-mid);
            font-family: var(--font-body);
            font-size: 1rem;
            width: 100%;
            outline: none;
            transition: all 0.2s ease;
        }

        .login-form input[type="email"]:focus,
        .login-form input[type="password"]:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(201,168,76,0.3);
        }

        .login-form input[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
        }

        .login-form .remember-me {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-body);
        }

        .login-form button {
            padding: 0.85rem 1rem;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--paper);
            background: var(--gold);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.25s;
        }

        .login-form button:hover {
            background: var(--gold-light);
        }

        .login-right {
            position: relative;
            height: 420px;
        }

        .doc-stack { position: absolute; inset: 0, pointer-events:none;}
        .doc-card {
            position: absolute;
            background: var(--paper);
            border: 1.5px solid var(--paper-mid);
            padding: 1.5rem;
            box-shadow: 4px 4px 0 var(--paper-mid), 0 16px 48px rgba(13,17,23,0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            pointer-events: none;
        }
        .doc-card:nth-child(1) { width: 300px; top: 60px; left: 40px; transform: rotate(4deg); z-index:1; }
        .doc-card:nth-child(2) { width: 310px; top: 30px; left: 20px; transform: rotate(-2deg); z-index:2; }
        .doc-card:nth-child(3) { width: 320px; top: 0; left: 0; transform: rotate(1deg); z-index:3; }

        @media (max-width:900px){
            .login-container { grid-template-columns: 1fr; gap:2rem; padding:2rem; }
            .login-right { height: 300px; position:relative; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Hero / Login Description -->
        <div class="hero-left">
            <div class="hero-eyebrow">
                <div class="hero-eyebrow-line"></div>
                <span class="hero-eyebrow-text">Secure Document Platform</span>
            </div>

            <h1 class="hero-title">
                Sign In<br>to <em>DOCSYSTEM</em>
            </h1>

            <p class="hero-subtitle">
                Enter your credentials below to access your dashboard, upload documents, approve, and digitally sign securely.
            </p>
        </div>

        <!-- Login Form -->
        <div class="login-right">
            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf
                <h2>Login</h2>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="remember-me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Remember me</label>
                </div>

                @if (Route::has('password.request'))
                    <a class="text-sm text-blue-acc underline mb-2 block" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif

                <button type="submit">Log In</button>
            </form>
        </div>

        <!-- Document Stack Visual -->
        <div class="doc-stack">
            <div class="doc-card"></div>
            <div class="doc-card"></div>
            <div class="doc-card"></div>
        </div>
    </div>
</body>
</html>