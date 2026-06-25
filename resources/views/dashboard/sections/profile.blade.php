<div id="section-profile" class="dashboard-section">
    <div class="documents-card">

        <div class="upload-modal-body" style="padding:24px;">
            @if(session('profile_success'))
                <div class="alert alert-success">
                    {{ session('profile_success') }}
                </div>
            @endif

            @if(session('profile_error'))
                <div class="alert alert-error">
                    {{ session('profile_error') }}
                </div>
            @endif

            @if(session('signature_success'))
                <div class="alert alert-success">
                    {{ session('signature_success') }}
                </div>
            @endif

            @if(session('signature_error'))
                <div class="alert alert-error">
                    {{ session('signature_error') }}
                </div>
            @endif

            @if($errors->signature->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->signature->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="profileForm"
                    method="POST"
                    action="{{ route('profile.update') }}"
                    class="upload-form">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label>Name</label>
                    <input type="text"
                            name="name"
                            value="{{ auth()->user()->name }}"
                            >
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password"
                            id="password"
                            name="password"
                            placeholder="Leave blank if unchanged"
                            autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password', 'eyeIcon1')" title="Toggle password visibility">
                        <i id="eyeIcon1" class="bi bi-eye-fill"></i>
                    </button>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password_confirmation', 'eyeIcon2')" title="Toggle password visibility">
                        <i id="eyeIcon2" class="bi bi-eye-fill"></i>
                    </button>
                </div>
                <div id="passwordMatchMessage"
                    class="password-match-message">
                </div>
                
                <div class="password-rules" id="passwordRules">
                    <div id="ruleLength" class="rule invalid">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Minimum 12 characters
                    </div>

                    <div id="ruleLetter" class="rule invalid">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Contains letters
                    </div>

                    <div id="ruleCase" class="rule invalid">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Contains uppercase and lowercase
                    </div>

                    <div id="ruleNumber" class="rule invalid">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Contains a number
                    </div>

                    <div id="ruleSymbol" class="rule invalid">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Contains a symbol (!@#$%^&*)
                    </div>
                </div>
                
                <button type="button"
                        class="btn-submit"
                        onclick="openSignatureChoiceModal()">
                    Signature
                </button>

                @if(auth()->user()->signature_path)
                    <div class="signature-preview-card">
                        <h3>Current Signature</h3>

                        <img src="{{ route('signatures.view', encrypt(auth()->id())) }}"
                                class="signature-preview"
                                alt="Signature">
                    </div>
                @endif

                <button type="submit"
                        class="btn-submit">
                    Save Profile
                </button>
            </form>
            
            <div id="profileErrorToast"
                class="profile-toast">

                <div class="toast-header">
                    <strong class="me-auto">Error</strong>
                    <div class="toast-body">
                        Please correct the errors and try again.
                    </div>
                </div>

                <div class="toast-body">
                    Please correct the errors and try again.
                </div>

            </div>

            @if($errors->any())
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                console.log('Laravel Errors:');
                    console.log(@json($errors->all()));
                    const toast =
                    document.getElementById('profileErrorToast');

                toast.querySelector('.toast-body').innerHTML =
                    @json(implode('<br>', $errors->all()));

                toast.classList.add('show-toast');

                setTimeout(() => {
                    toast.classList.remove('show-toast');
                }, 5000);

                });
                </script>
            @endif
        </div>
    </div>
</div>