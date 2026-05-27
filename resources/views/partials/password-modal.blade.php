{{-- PASSWORD CONFIRMATION MODAL --}}
<div id="passwordModal"
     class="password-modal">
    <div class="password-modal-content">
        
        {{-- Header --}}
        <div class="password-modal-header">
            <div class="password-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#2563eb" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div class="password-modal-text">
                <h2 class="password-modal-title">Confirm Password</h2>
                <p class="password-modal-subtitle">Please verify your account password to continue.</p>
            </div>
        </div>

        {{-- Input Body --}}
        <div class="password-modal-body">
            <label class="password-label" for="modalPassword">Account Password</label>
            <div class="password-input-wrapper">
                <input type="password"
                       id="modalPassword"
                       class="password-input"
                       placeholder="Enter your password"
                       autocomplete="current-password"
                       onkeydown="if(event.key === 'Enter') submitProtectedForm()">
                
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Toggle password visibility">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b7280" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
           <div id="modalErrorMessage"
     class="password-error-message"
     style="{{ session('password_modal_error') ? 'display:block;' : '' }}">

    {{ session('password_modal_error') }}

</div>
        </div>

        {{-- Action Buttons --}}
        <div class="password-modal-actions">
            <button type="button" class="password-btn-cancel" onclick="closePasswordModal()">
                Cancel
            </button>
            <button type="button" class="password-btn-confirm" onclick="submitProtectedForm()">
                Confirm Action
            </button>
        </div>

    </div>
</div>

<style>
/* Global box-sizing safety rule for your modal tree */
.password-modal, .password-modal * {
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.password-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45); /* modern slate backdrop opacity */
    backdrop-filter: blur(6px); /* smoother background blur */
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
}

.password-modal-content {
    width: 100%;
    max-width: 440px;
    background: #ffffff;
    border-radius: 16px; /* modern slight roundness standard */
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    animation: modalPop .2s cubic-bezier(0.34, 1.56, 0.64, 1); /* slight bounce physics */
}

.password-modal-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
}

.password-modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.password-modal-text {
    flex-grow: 1;
}

.password-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.password-modal-subtitle {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.4;
}

.password-label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.password-input {
    width: 100%;
    padding: 11px 40px 11px 14px; /* leaves right padding for toggle button */
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #1f2937;
    transition: all .15s ease;
    outline: none;
}

.password-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
}

.password-toggle-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.password-error-message {
    color: #dc2626;
    font-size: 12px;
    margin-top: 6px;
    display: none; /* hidden until triggered */
}

.password-modal-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.password-btn-cancel, .password-btn-confirm {
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s ease;
}

.password-btn-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.password-btn-cancel:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.password-btn-confirm {
    background: #2563eb;
    color: white;
}

.password-btn-confirm:hover {
    background: #1d4ed8;
}

@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<script>
window.protectedForm = null;

function openPasswordModal(event, form) {
    event.preventDefault();

    window.protectedForm = form;

    document.getElementById('passwordModal').style.display = 'flex';
    document.getElementById('modalPassword').value = '';

    return false;
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    window.protectedForm = null;
}

function submitProtectedForm() {
    if (!window.protectedForm) {
        alert('No form selected.');
        return;
    }

    const passwordInput = document.getElementById('modalPassword');
    const password = passwordInput.value.trim();

    if (!password) {
        alert('Password is required.');
        passwordInput.focus();
        return;
    }

    const hiddenInput = window.protectedForm.querySelector('.password-hidden-input');

    if (!hiddenInput) {
        alert('Password hidden input missing.');
        return;
    }

    hiddenInput.value = password;

    if (window.protectedForm.id === 'drawSignatureForm') {
        saveDrawnSignature();
    }

    window.protectedForm.submit();
}

function togglePasswordVisibility() {
    const input = document.getElementById('modalPassword');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>