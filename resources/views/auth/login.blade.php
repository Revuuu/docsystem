<x-guest-layout>

<div class="login-header">
    <h2>Welcome Back</h2>
    <p>Get your documents all signed and ready.</p>
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
            <i id="eyeIcon" class="bi bi-eye-fill"></i>
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
</x-guest-layout>