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