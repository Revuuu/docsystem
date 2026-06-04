{{-- PASSWORD CONFIRMATION MODAL --}}
<div id="passwordModal"
     class="password-modal">
    <div class="password-modal-content">
        
        {{-- Header --}}
        <div class="password-modal-header">
            <div class="password-modal-icon">
                <i class="bi bi-lock-fill"></i>
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
                    <i id="eyeIcon" class="bi bi-eye-fill"></i>
                </button>
            </div>
            <div id="modalErrorMessage"
                class="password-error-message"
                style="display:none;">
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

    const modal = document.getElementById('passwordModal');
    const passwordInput = document.getElementById('modalPassword');
    const errorMessage = document.getElementById('modalErrorMessage');

    if (passwordInput) {
        passwordInput.value = '';
    }

    if (errorMessage) {
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
    }

    modal.style.display = 'flex';

    setTimeout(() => {
        passwordInput?.focus();
    }, 50);

    return false;
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    const passwordInput = document.getElementById('modalPassword');
    const errorMessage = document.getElementById('modalErrorMessage');

    modal.style.display = 'none';
    window.protectedForm = null;

    if (passwordInput) {
        passwordInput.value = '';
    }

    if (errorMessage) {
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
    }
}

async function submitProtectedForm() {
    if (!window.protectedForm) {
        alert('No form selected.');
        return;
    }

    const passwordInput = document.getElementById('modalPassword');
    const errorMessage = document.getElementById('modalErrorMessage');
    const confirmButton = document.querySelector('.password-btn-confirm');

    const password = passwordInput.value.trim();

    if (!password) {
        errorMessage.textContent = 'Password is required.';
        errorMessage.style.display = 'block';
        passwordInput.focus();
        return;
    }

    try {
        confirmButton.disabled = true;
        confirmButton.textContent = 'Verifying...';

        const response = await fetch('/verify-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({
                password: password,
            }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            errorMessage.textContent = data.message || 'Incorrect account password.';
            errorMessage.style.display = 'block';

            passwordInput.value = '';
            passwordInput.focus();

            return;
        }

        const hiddenInput = window.protectedForm.querySelector('.password-hidden-input');

        if (!hiddenInput) {
            errorMessage.textContent = 'Password hidden input missing.';
            errorMessage.style.display = 'block';
            return;
        }

        hiddenInput.value = password;

        if (window.protectedForm.id === 'drawSignatureForm') {
            saveDrawnSignature();
        }

        window.protectedForm.submit();

    } catch (error) {
        errorMessage.textContent = 'Unable to verify password. Please try again.';
        errorMessage.style.display = 'block';
    } finally {
        confirmButton.disabled = false;
        confirmButton.textContent = 'Confirm Action';
    }
}

function togglePasswordVisibility() {

    const input =
        document.getElementById(
            'modalPassword'
        );

    const eyeIcon =
        document.getElementById(
            'eyeIcon'
        );

   if (input.type === 'password') {
        input.type = 'text';
       
        eyeIcon.classList.remove(
            'bi-eye-fill'
        );

        eyeIcon.classList.add(
            'bi-eye-slash-fill'
        );
    } else {
        input.type = 'password';
        
        eyeIcon.classList.remove(
            'bi-eye-slash-fill'
        );

        eyeIcon.classList.add(
            'bi-eye-fill'
        );
    }
}
</script>