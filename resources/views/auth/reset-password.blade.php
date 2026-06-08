<x-guest-layout>

<div class="login-header">

    <h2>Create Password</h2>

    <p>
        Set your account password to continue
    </p>

</div>

<form id="resetPasswordForm"
      method="POST"
      action="{{ route('password.store') }}">

    @csrf

    <input type="hidden"
           name="token"
           value="{{ $request->route('token') }}">

    <div class="form-group">

        <label>Email Address</label>

        <input type="email"
               name="email"
               value="{{ old('email', $request->email) }}"
               readonly>

    </div>

    <div class="form-group">

        <label>New Password</label>

        <input type="password"
               id="password"
               name="password"
               required>

        <button type="button"
                class="password-toggle-btn"
                onclick="togglePassword('password','eye1')">

            <i id="eye1"
               class="bi bi-eye-fill"></i>

        </button>

    </div>

    <div class="form-group">

        <label>Confirm Password</label>

        <input type="password"
               id="password_confirmation"
               name="password_confirmation"
               required>

        <button type="button"
                class="password-toggle-btn"
                onclick="togglePassword('password_confirmation','eye2')">

            <i id="eye2"
               class="bi bi-eye-fill"></i>

        </button>

    </div>

    <div id="passwordMatchMessage"
         class="password-match-message">
    </div>

    @error('password')
        <div class="login-error-box">
            <p>{{ $message }}</p>
        </div>
    @enderror

    <div class="password-rules"
         id="passwordRules">

        <div id="ruleLength"
             class="rule invalid">
            <i class="bi bi-x-circle-fill"></i>
            Minimum 12 characters
        </div>

        <div id="ruleLetter"
             class="rule invalid">
            <i class="bi bi-x-circle-fill"></i>
            Contains letters
        </div>

        <div id="ruleCase"
             class="rule invalid">
            <i class="bi bi-x-circle-fill"></i>
            Contains uppercase and lowercase
        </div>

        <div id="ruleNumber"
             class="rule invalid">
            <i class="bi bi-x-circle-fill"></i>
            Contains a number
        </div>

        <div id="ruleSymbol"
             class="rule invalid">
            <i class="bi bi-x-circle-fill"></i>
            Contains a symbol (!@#$%^&*)
        </div>

    </div>

    <button type="submit"
            class="login-btn">
        CREATE PASSWORD
    </button>

</form>

</x-guest-layout>