import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc =
    new URL(
        'pdfjs-dist/build/pdf.worker.min.mjs',
        import.meta.url
    ).toString();

const Signature = {

    state: {
        sigX: 0,
        sigY: 0,
        placed: false,
        currentPdf: null,
        currentPdfUrl: null,
        currentPage: 1
    },

    init() {

        this.ghost =
            document.getElementById('sigGhost');

        this.layer =
            document.getElementById('pdfClickLayer');

        this.wrapper =
            document.getElementById('pdfWrapper');

        this.canvas =
            document.getElementById('pdfCanvas');

        this.btn =
            document.getElementById('btnConfirm');

        this.hint =
            document.getElementById('hintText');

        this.pageInput =
            document.getElementById('sigPageInput');

        this.bindEvents();
    },

    bindEvents() {

        if (this.pageInput) {

            this.pageInput.addEventListener(
                'change',
                async () => {

                    if (!this.state.currentPdf) {
                        return;
                    }

                    let page =
                        parseInt(
                            this.pageInput.value
                        );

                    if (page < 1) {
                        page = 1;
                    }

                    if (
                        page >
                        this.state.currentPdf.numPages
                    ) {
                        page =
                            this.state.currentPdf
                                .numPages;
                    }

                    this.state.currentPage =
                        page;

                    this.pageInput.value =
                        page;

                    if (this.ghost) {
                        this.ghost.style.display =
                            'none';
                    }

                    this.state.placed =
                        false;

                    if (this.btn) {
                        this.btn.disabled =
                            true;
                    }

                    await this.renderPage(
                        page
                    );

                }
            );
        }

        if (
            this.layer &&
            this.canvas &&
            this.ghost
        ) {

            this.layer.addEventListener(
                'click',
                (e) => {

                    const rect =
                        this.canvas
                            .getBoundingClientRect();

                    this.state.sigX =
                        e.clientX -
                        rect.left;

                    this.state.sigY =
                        e.clientY -
                        rect.top;

                    this.ghost.style.left =
                        this.state.sigX +
                        'px';

                    this.ghost.style.top =
                        this.state.sigY +
                        'px';

                    this.ghost.style.display =
                        'block';

                    this.state.placed =
                        true;

                    if (this.btn) {
                        this.btn.disabled =
                            false;
                    }

                    if (this.hint) {

                        this.hint.textContent =
                            'Signature placed! Click again to reposition or confirm.';

                    }

                }
            );
        }
    },

    checkSignature(
        hasSignature,
        docId,
        pdfUrl,
        signUrl
    ) {

        if (!hasSignature) {

            alert(
                'You do not have a signature uploaded yet. Please upload or draw your signature first.'
            );

            return;
        }

        this.open(
            docId,
            pdfUrl,
            signUrl
        );
    },

    async open(
        docId,
        pdfUrl,
        signUrl
    ) {

        this.state.currentPdfUrl =
            pdfUrl;

        this.state.currentPage =
            1;

        const form =
            document.getElementById(
                'signatureForm'
            );

        if (form) {
            form.action = signUrl;
        }

        if (this.pageInput) {
            this.pageInput.value = 1;
        }

        if (this.ghost) {
            this.ghost.style.display =
                'none';
        }

        this.state.placed =
            false;

        if (this.btn) {
            this.btn.disabled = true;
        }

        if (this.hint) {

            this.hint.textContent =
                'Click anywhere on the document to place your signature';

        }

        document
            .getElementById(
                'signModal'
            )
            ?.classList
            .add('active');

        this.state.currentPdf =
            await pdfjsLib
                .getDocument(pdfUrl)
                .promise;

        await this.renderPage(1);
    },

    async renderPage(pageNumber) {

        if (
            !this.state.currentPdf ||
            !this.canvas
        ) {
            return;
        }

        const page =
            await this.state.currentPdf
                .getPage(pageNumber);

        const viewport =
            page.getViewport({
                scale: 1.5
            });

        const context =
            this.canvas.getContext('2d');

        this.canvas.width =
            viewport.width;

        this.canvas.height =
            viewport.height;

        this.canvas.style.width =
            viewport.width + 'px';

        this.canvas.style.height =
            viewport.height + 'px';

        if (this.wrapper) {

            this.wrapper.style.width =
                viewport.width + 'px';

            this.wrapper.style.height =
                viewport.height + 'px';
        }

        if (this.layer) {

            this.layer.style.width =
                viewport.width + 'px';

            this.layer.style.height =
                viewport.height + 'px';
        }

        await page.render({
            canvasContext: context,
            viewport
        }).promise;
    },

    close() {

        document
            .getElementById(
                'signModal'
            )
            ?.classList
            .remove('active');

        if (this.canvas) {

            const context =
                this.canvas.getContext(
                    '2d'
                );

            context.clearRect(
                0,
                0,
                this.canvas.width,
                this.canvas.height
            );
        }
    },

    submit() {

        if (
            !this.state.placed ||
            !this.canvas
        ) {
            return;
        }

        const canvasW =
            this.canvas.clientWidth;

        const canvasH =
            this.canvas.clientHeight;

        const actualSigW = 300;
        const actualSigH = 180;

        document.getElementById(
            'formSigX'
        ).value =
            (
                this.state.sigX /
                canvasW
            ).toFixed(6);

        document.getElementById(
            'formSigY'
        ).value =
            (
                this.state.sigY /
                canvasH
            ).toFixed(6);

        document.getElementById(
            'formSigW'
        ).value =
            (
                actualSigW /
                canvasW
            ).toFixed(6);

        document.getElementById(
            'formSigH'
        ).value =
            (
                actualSigH /
                canvasH
            ).toFixed(6);

        document.getElementById(
            'formSigPage'
        ).value =
            this.state.currentPage;

        document
            .getElementById(
                'signatureForm'
            )
            ?.submit();
    }

};

/*
|--------------------------------------------------------------------------
| Blade Compatibility
|--------------------------------------------------------------------------
*/

window.openSignModal =
    (...args) =>
        Signature.open(...args);

window.closeSignModal =
    () =>
        Signature.close();

window.submitSignature =
    () =>
        Signature.submit();

window.checkSignatureAndOpenModal =
    (...args) =>
        Signature.checkSignature(
            ...args
        );

export default Signature;