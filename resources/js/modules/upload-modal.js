const UploadModal = {

    init()
    {
        window.addEventListener(
            'click',
            (e) => {

                const modal =
                    document.getElementById(
                        'uploadModal'
                    );

                if (
                    e.target === modal
                ) {
                    this.close();
                }
            }
        );

        // File size validation
        const fileInput =
            document.querySelector(
                'input[name="file"]'
            );

        fileInput?.addEventListener(
            'change',
            function () {

                const file =
                    this.files[0];

                const error =
                    document.getElementById(
                        'fileError'
                    );

                if (!file) {

                    if (error) {
                        error.style.display =
                            'none';
                    }

                    return;
                }

                const maxSize =
                    10 * 1024 * 1024; // 10 MB

                if (
                    file.size > maxSize
                ) {

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

    open()
    {
        document
            .getElementById(
                'uploadModal'
            )
            ?.classList
            .add('show');
    },

    close()
    {
        document
            .getElementById(
                'uploadModal'
            )
            ?.classList
            .remove('show');
    }
};

window.openUploadModal =
    () => UploadModal.open();

window.closeUploadModal =
    () => UploadModal.close();

export default UploadModal;