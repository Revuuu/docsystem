<x-guest-layout>

<div class="login-header">
    <h2>Verify Login</h2>

    <p>
        A 6-digit verification code has been sent to
        <strong>{{ auth()->user()->email }}</strong>
    </p>
</div>

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
            class="login-btn">
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

</x-guest-layout>