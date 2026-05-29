const PdfViewer = {

    init()
    {
        document.addEventListener(
            'click',
            (e) => {

                const modal =
                    document.getElementById(
                        'pdfModal'
                    );

                if (
                    modal &&
                    e.target === modal
                ) {
                    this.close();
                }
            }
        );
    },

    open(url)
    {
        const modal =
            document.getElementById(
                'pdfModal'
            );

        const frame =
            document.getElementById(
                'pdfFrame'
            );

        if (!modal || !frame) return;

        frame.src = url;

        modal.classList.add('active');
    },

    close()
    {
        const modal =
            document.getElementById(
                'pdfModal'
            );

        const frame =
            document.getElementById(
                'pdfFrame'
            );

        if (!modal || !frame) return;

        frame.src = '';

        modal.classList.remove('active');
    }
};

window.openPdfModal =
    (url) => PdfViewer.open(url);

window.closePdfModal =
    () => PdfViewer.close();

export default PdfViewer;