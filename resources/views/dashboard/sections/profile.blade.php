<div id="section-profile" class="dashboard-section">

    <div class="profile-page">

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

        <div class="profile-stack">

            {{-- ACCOUNT SETTINGS --}}
            <div class="documents-card profile-card">

                <div class="profile-card-header">
                    <div>
                        <h3>
                            <i class="bi bi-person-circle"></i>
                            Account Settings
                        </h3>

                        <p>
                            Update your name and password.
                        </p>
                    </div>
                </div>

                <div class="profile-card-body">

                    <form id="resetPasswordForm"
                          method="POST"
                          action="{{ route('profile.update') }}"
                          class="upload-form">

                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label>Name</label>

                            <input type="text"
                                   name="name"
                                   value="{{ auth()->user()->name }}">
                        </div>

                        <div class="form-group">
                            <label>New Password</label>

                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="Leave blank if unchanged"
                                   autocomplete="new-password">

                            <button type="button"
                                    class="password-toggle-btn"
                                    onclick="togglePassword('password', 'eyeIcon1')"
                                    title="Toggle password visibility">
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

                            <button type="button"
                                    class="password-toggle-btn"
                                    onclick="togglePassword('password_confirmation', 'eyeIcon2')"
                                    title="Toggle password visibility">
                                <i id="eyeIcon2" class="bi bi-eye-fill"></i>
                            </button>
                        </div>

                        <div id="passwordMatchMessage"
                             class="password-match-message">
                        </div>

                        <div class="password-rules"
                             id="passwordRules">

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

                        <div class="profile-form-actions">
                            <button type="submit"
                                    class="btn-submit">
                                Save Profile
                            </button>
                        </div>

                    </form>

                </div>

            </div>

            {{-- DIGITAL SIGNATURE --}}
            <div class="documents-card profile-card">

                <div class="profile-card-header">
                    <div>
                        <h3>
                            <i class="bi bi-pen"></i>
                            Digital Signature
                        </h3>

                        <p>
                            Used when approving documents.
                        </p>
                    </div>
                </div>

                <div class="profile-card-body">

                    @if(auth()->user()->signature_path)

                        <div class="signature-profile-layout">

                            <div class="signature-preview-panel">
                                <span class="signature-label">
                                    Current Signature
                                </span>

                                <div class="signature-image-box">
                                    <img src="{{ route('signatures.view', encrypt(auth()->id())) }}"
                                         class="signature-preview"
                                         alt="Signature">
                                </div>
                            </div>

                            <div class="signature-details">
                                <h4>
                                    Signature on file
                                </h4>

                                <p>
                                    This signature is used when you approve and sign documents in the workflow.
                                </p>
                            </div>

                        </div>

                    @else

                        <div class="empty-signature">
                            <i class="bi bi-pen"></i>

                            <strong>
                                No signature uploaded
                            </strong>

                            <p>
                                Add a signature before approving documents.
                            </p>
                        </div>

                    @endif

                    <div class="signature-card-actions">
                        <button type="button"
                                class="btn-submit"
                                onclick="openSignatureChoiceModal()">
                            Change Signature
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <div id="profileErrorToast"
             class="profile-toast">

            <div class="toast-header">
                <strong class="me-auto">Error</strong>
            </div>

            <div class="toast-body">
                Please correct the errors and try again.
            </div>

        </div>

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
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