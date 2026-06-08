const Auth = {

    init() {
        this.initializePasswordToggles();
        this.initializeResetPasswordValidation();
        this.initializeOtpPage();
    },

    initializePasswordToggles() {

        window.togglePassword = function (id, iconId) {

            const input = document.getElementById(id);
            const icon = document.getElementById(iconId);

            if (!input || !icon) return;

            if (input.type === 'password') {

                input.type = 'text';

                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');

            } else {

                input.type = 'password';

                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        };

        window.togglePasswordVisibility = function () {

            const input =
                document.getElementById('loginPassword');

            const icon =
                document.getElementById('eyeIcon');

            if (!input || !icon) return;

            if (input.type === 'password') {

                input.type = 'text';

                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');

            } else {

                input.type = 'password';

                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        };
    },

    initializeResetPasswordValidation() {

        const form =
            document.getElementById('resetPasswordForm');

        const password =
            document.getElementById('password');

        const confirmation =
            document.getElementById('password_confirmation');

        if (!form || !password || !confirmation) {
            return;
        }

        const matchMessage =
            document.getElementById('passwordMatchMessage');

        form.addEventListener('submit', function (e) {

            if (
                password.value &&
                password.value !== confirmation.value
            ) {

                e.preventDefault();

                if (matchMessage) {

                    matchMessage.innerHTML =
                        '<i class="bi bi-exclamation-circle-fill text-danger"></i> Passwords do not match';
                }
            }
        });

        password.addEventListener(
            'input',
            validatePasswordRules
        );

        password.addEventListener(
            'input',
            checkPasswordMatch
        );

        confirmation.addEventListener(
            'input',
            checkPasswordMatch
        );

        function validatePasswordRules() {

            const value = password.value;

            updateRule(
                'ruleLength',
                value.length >= 12
            );

            updateRule(
                'ruleLetter',
                /[a-zA-Z]/.test(value)
            );

            updateRule(
                'ruleCase',
                /[a-z]/.test(value) &&
                /[A-Z]/.test(value)
            );

            updateRule(
                'ruleNumber',
                /\d/.test(value)
            );

            updateRule(
                'ruleSymbol',
                /[^A-Za-z0-9]/.test(value)
            );
        }

        function checkPasswordMatch() {

            if (!matchMessage) return;

            if (confirmation.value.length === 0) {

                matchMessage.innerHTML = '';
                return;
            }

            if (password.value === confirmation.value) {

                matchMessage.innerHTML =
                    '<i class="bi bi-check-circle-fill text-success"></i> Passwords match';

            } else {

                matchMessage.innerHTML =
                    '<i class="bi bi-exclamation-circle-fill text-danger"></i> Passwords do not match';
            }
        }

        function updateRule(id, valid) {

            const rule =
                document.getElementById(id);

            if (!rule) return;

            const icon =
                rule.querySelector('i');

            if (!icon) return;

            if (valid) {

                rule.classList.remove('invalid');
                rule.classList.add('valid');

                icon.classList.remove(
                    'bi-x-circle-fill',
                    'text-danger'
                );

                icon.classList.add(
                    'bi-check-circle-fill',
                    'text-success'
                );

            } else {

                rule.classList.remove('valid');
                rule.classList.add('invalid');

                icon.classList.remove(
                    'bi-check-circle-fill',
                    'text-success'
                );

                icon.classList.add(
                    'bi-x-circle-fill',
                    'text-danger'
                );
            }
        }
    },
    
    initializeOtpPage() {

    const inputs =
        document.querySelectorAll('.otp-input');

    const hiddenInput =
        document.getElementById('otp');

    if (!inputs.length || !hiddenInput) {
        return;
    }

    inputs.forEach((input,index)=>{

        input.addEventListener('input',e=>{

            e.target.value =
                e.target.value.replace(/\D/g,'');

            if(
                e.target.value &&
                index < inputs.length - 1
            ){
                inputs[index+1].focus();
            }

            updateOtp();
        });

        input.addEventListener('keydown',e=>{

            if(
                e.key === 'Backspace' &&
                !e.target.value &&
                index > 0
            ){
                inputs[index-1].focus();
            }
        });
    });

    function updateOtp(){

        let otp='';

        inputs.forEach(input=>{
            otp += input.value;
        });

        hiddenInput.value = otp;
    }

    inputs[0].focus();

    const countdown =
        document.getElementById('countdown');

    const resendBtn =
        document.getElementById('resendBtn');

    if (countdown && resendBtn) {

        let seconds = 30;

        const timer = setInterval(() => {

            seconds--;

            countdown.textContent = seconds;

            if (seconds <= 0) {

                clearInterval(timer);

                resendBtn.disabled = false;
            }

        },1000);
    }
}
};

export default Auth;