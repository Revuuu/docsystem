const UploadModal = {

    init()
    {
        window.addEventListener(
            'click',
            (e) => {
                const modal =
                    document.getElementById('uploadModal');

                if (e.target === modal) {
                    this.close();
                }
            }
        );

        this.bindFileValidation();
        this.bindSubmitLoading();
    },

    bindFileValidation()
    {
        const fileInput =
            document.querySelector('input[name="file"]');

        fileInput?.addEventListener(
            'change',
            function () {
                const file =
                    this.files[0];

                const error =
                    document.getElementById('fileError');

                if (!file) {
                    if (error) {
                        error.style.display = 'none';
                    }

                    return;
                }

                const maxSize =
                    10 * 1024 * 1024;

                if (file.size > maxSize) {
                    if (error) {
                        error.textContent =
                            'The uploaded file exceeds the maximum allowed size of 10 MB.';

                        error.style.display =
                            'block';
                    }

                    this.value = '';

                    return;
                }

                if (error) {
                    error.style.display =
                        'none';
                }
            }
        );
    },

    bindSubmitLoading()
    {
        const form =
            document.querySelector('.upload-form');

        const submitButton =
            form?.querySelector('.btn-submit');

        if (!form || !submitButton) return;

        form.addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.classList.add('is-loading');

            const text =
                submitButton.querySelector('.btn-text');

            if (text) {
                text.textContent = 'Uploading...';
            }
        });
    },

    open()
    {
        document
            .getElementById('uploadModal')
            ?.classList
            .add('show');
    },

    close()
    {
        document
            .getElementById('uploadModal')
            ?.classList
            .remove('show');
    }
};

window.openUploadModal =
    () => UploadModal.open();

window.closeUploadModal =
    () => UploadModal.close();

export default UploadModal;