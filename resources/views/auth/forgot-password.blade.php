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