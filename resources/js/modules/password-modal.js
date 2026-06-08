const PasswordModal = {

    protectedForm: null,

    init() {

        window.openPasswordModal =
            this.open.bind(this);

        window.closePasswordModal =
            this.close.bind(this);

        window.submitProtectedForm =
            this.submit.bind(this);
    },

    open(event, form) {

        event.preventDefault();

        this.protectedForm = form;

        const modal =
            document.getElementById('passwordModal');

        const passwordInput =
            document.getElementById('modalPassword');

        const errorMessage =
            document.getElementById('modalErrorMessage');

        if (!modal) {
            return false;
        }

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
    },

    close() {

        const modal =
            document.getElementById('passwordModal');

        const passwordInput =
            document.getElementById('modalPassword');

        const errorMessage =
            document.getElementById('modalErrorMessage');

        if (modal) {
            modal.style.display = 'none';
        }

        this.protectedForm = null;

        if (passwordInput) {
            passwordInput.value = '';
        }

        if (errorMessage) {

            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
        }
    },

    async submit() {

        if (!this.protectedForm) {

            alert('No form selected.');
            return;
        }

        const passwordInput =
            document.getElementById('modalPassword');

        const errorMessage =
            document.getElementById('modalErrorMessage');

        const confirmButton =
            document.querySelector('.password-btn-confirm');

        const password =
            passwordInput?.value.trim();

        if (!password) {

            errorMessage.textContent =
                'Password is required.';

            errorMessage.style.display =
                'block';

            passwordInput?.focus();

            return;
        }

        try {

            confirmButton.disabled = true;
            confirmButton.textContent = 'Verifying...';

            const response = await fetch(
                '/verify-password',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                    },
                    body: JSON.stringify({
                        password
                    })
                }
            );

            const data =
                await response.json();

            if (!response.ok || !data.success) {

                errorMessage.textContent =
                    data.message ||
                    'Incorrect account password.';

                errorMessage.style.display =
                    'block';

                passwordInput.value = '';
                passwordInput.focus();

                return;
            }

            const hiddenInput =
                this.protectedForm.querySelector(
                    '.password-hidden-input'
                );

            if (!hiddenInput) {

                console.error(
                    'Missing .password-hidden-input in form:',
                    this.protectedForm
                );

                errorMessage.textContent =
                    'Password hidden input missing.';

                errorMessage.style.display =
                    'block';

                return;
            }

            hiddenInput.value = password;

            if (
                this.protectedForm.id ===
                'drawSignatureForm'
            ) {

                if (
                    typeof saveDrawnSignature ===
                    'function'
                ) {
                    saveDrawnSignature();
                }
            }

            this.protectedForm.submit();

        } catch (error) {

            console.error(error);

            errorMessage.textContent =
                'Unable to verify password. Please try again.';

            errorMessage.style.display =
                'block';

        } finally {

            if (confirmButton) {

                confirmButton.disabled = false;
                confirmButton.textContent =
                    'Confirm Action';
            }
        }
    }
};

export default PasswordModal;