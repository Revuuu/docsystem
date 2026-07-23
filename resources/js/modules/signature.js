import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

const Signature = {

    state: {
        sigX: 0,
        sigY: 0,
        placed: false,
        currentPdf: null,
        currentPdfUrl: null,
        currentPage: 1,
        isFixedTemplate: false,
        renderTask: null,
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

        this.pageLabel =
            document.querySelector(
                'label[for="sigPageInput"]'
            );

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

                    let page = Number.parseInt(
                        this.pageInput.value,
                        10
                    );

                    if (!Number.isFinite(page)) {
                        page = 1;
                    }

                    page = Math.min(
                        Math.max(page, 1),
                        this.state.currentPdf.numPages
                    );

                    this.state.currentPage = page;
                    this.pageInput.value = page;

                    /*
                    * Ordinary PDFs require the user to place
                    * the signature again after changing pages.
                    */
                    if (!this.state.isFixedTemplate) {
                        if (this.ghost) {
                            this.ghost.style.display =
                                'none';
                        }

                        this.state.placed = false;

                        if (this.btn) {
                            this.btn.disabled = true;
                        }

                        if (this.hint) {
                            this.hint.textContent =
                                'Click anywhere on the document to place your signature';
                        }
                    } else {
                        /*
                        * Purchase Orders use automatic placement,
                        * so changing pages must not disable approval.
                        */
                        this.state.placed = true;

                        if (this.btn) {
                            this.btn.disabled = false;
                        }
                    }

                    await this.renderPage(page);
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
                    if (this.state.isFixedTemplate) {
                        return;
                    }

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
    signUrl,
    templateKey = null
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
        signUrl,
        templateKey
    );
},

async open(
    docId,
    pdfUrl,
    signUrl,
    templateKey = null
) {

    const normalizedTemplateKey =
    typeof templateKey === 'string'
        ? templateKey.trim()
        : '';

    this.state.isFixedTemplate =
        normalizedTemplateKey !== '';

   console.log('SIGNATURE OPEN DEBUG', {
    docId,
    pdfUrl,
    signUrl,
    templateKey: normalizedTemplateKey,
    isFixedTemplate:
        this.state.isFixedTemplate,
});

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

    const modal =
        document.getElementById(
            'signModal'
        );

    const modalTitle =
        modal?.querySelector(
            '.modal-header h3'
        );

    const pageSelector =
        modal?.querySelector(
            '.page-selector'
        );

    if (this.pageInput) {
        this.pageInput.value = 1;
        this.pageInput.min = 1;
        this.pageInput.disabled = false;
    }

    if (this.ghost) {
        this.ghost.style.display =
            'none';
    }

    if (this.layer) {
        this.layer.style.pointerEvents =
            this.state.isFixedTemplate
                ? 'none'
                : 'auto';

        this.layer.style.cursor =
            this.state.isFixedTemplate
                ? 'default'
                : 'crosshair';
    }

    if (this.state.isFixedTemplate) {
        this.state.placed = true;

        if (this.btn) {
            this.btn.disabled = false;
        }

        if (modalTitle) {
            modalTitle.textContent =
                'Review and Confirm Signature';
        }

        if (pageSelector) {
            pageSelector.style.display = '';
        }

        if (this.hint) {
         this.hint.textContent =
            'Your signature will be placed automatically in your assigned signature field.';
        }
    } else {
        this.state.placed = false;

        if (this.btn) {
            this.btn.disabled = true;
        }

        if (modalTitle) {
            modalTitle.textContent =
                'Place Signature';
        }

        if (pageSelector) {
            pageSelector.style.display =
                '';
        }

        if (this.hint) {
            this.hint.textContent =
                'Click anywhere on the document to place your signature';
        }
    }

    modal?.classList.add('active');

    this.state.currentPdf =
    await pdfjsLib
        .getDocument(pdfUrl)
        .promise;

        console.log('SIGNATURE PDF LOADED', {
    pdfUrl,
    numPages: this.state.currentPdf.numPages,
});
    if (this.pageInput) {
        this.pageInput.min = 1;
        this.pageInput.max =
            this.state.currentPdf.numPages;
        this.pageInput.value = 1;
    }

    if (pageSelector) {
        pageSelector.style.display =
            this.state.currentPdf.numPages > 1
                ? ''
                : 'none';
    }

    this.updatePageIndicator();

    await this.renderPage(1);
},

updatePageIndicator() {
    if (
        !this.pageLabel ||
        !this.state.currentPdf
    ) {
        return;
    }

    this.pageLabel.textContent =
        `Page ${this.state.currentPage} ` +
        `of ${this.state.currentPdf.numPages}:`;
},
    async renderPage(pageNumber) {
    if (
        !this.state.currentPdf ||
        !this.canvas
    ) {
        return;
    }

    const totalPages =
        this.state.currentPdf.numPages;

    const safePage = Math.min(
        Math.max(Number(pageNumber) || 1, 1),
        totalPages
    );

    this.state.currentPage = safePage;

    if (this.pageInput) {
        this.pageInput.value = safePage;
        this.pageInput.disabled = true;
    }

    this.updatePageIndicator();

    /*
     * Cancel the active render before accessing
     * the same canvas for another page.
     */
    if (this.state.renderTask) {
        this.state.renderTask.cancel();

        try {
            await this.state.renderTask.promise;
        } catch (error) {
            if (
                error?.name !==
                'RenderingCancelledException'
            ) {
                throw error;
            }
        }

        this.state.renderTask = null;
    }

    const page =
        await this.state.currentPdf.getPage(
            safePage
        );

    const baseViewport =
        page.getViewport({
            scale: 1
        });

    const modalBox =
        document.querySelector(
            '#signModal .modal-box'
        );

    const availableWidth = Math.max(
        (modalBox?.clientWidth ?? 900) - 32,
        320
    );

    const scale = Math.min(
        1.5,
        availableWidth / baseViewport.width
    );

    const viewport =
        page.getViewport({
            scale
        });

    const context =
        this.canvas.getContext('2d');

    this.canvas.width =
        Math.ceil(viewport.width);

    this.canvas.height =
        Math.ceil(viewport.height);

    this.canvas.style.width =
        `${viewport.width}px`;

    this.canvas.style.height =
        `${viewport.height}px`;

    if (this.wrapper) {
        this.wrapper.style.width =
            `${viewport.width}px`;

        this.wrapper.style.height =
            `${viewport.height}px`;
    }

    if (this.layer) {
        this.layer.style.width =
            `${viewport.width}px`;

        this.layer.style.height =
            `${viewport.height}px`;
    }

    this.state.renderTask = page.render({
        canvasContext: context,
        viewport
    });

    try {
        await this.state.renderTask.promise;
    } catch (error) {
        if (
            error?.name !==
            'RenderingCancelledException'
        ) {
            throw error;
        }
    } finally {
        this.state.renderTask = null;

        if (this.pageInput) {
            this.pageInput.disabled = false;
        }
    }
},

    close() {
if (this.state.renderTask) {
    this.state.renderTask.cancel();
    this.state.renderTask = null;
}
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
        this.state.currentPdf = null;
        this.state.currentPdfUrl = null;
        this.state.currentPage = 1;
        this.state.placed = false;

        if (this.pageInput) {
            this.pageInput.value = 1;
            this.pageInput.max = 1;
        }
    },

    submit() {
    const form =
        document.getElementById(
            'signatureForm'
        );

    if (!form) {
        return;
    }

    /*
     * Purchase Order:
     * coordinates are resolved by the backend
     * from the assigned fixed signature block.
     */
    if (this.state.isFixedTemplate) {
        [
            'formSigX',
            'formSigY',
            'formSigW',
            'formSigH',
            'formSigPage',
        ].forEach((id) => {
            const input =
                document.getElementById(id);

            if (input) {
                input.value = '';
            }
        });

        form.submit();

        return;
    }

    /*
     * Ordinary PDF:
     * keep manual signature placement.
     */
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

    form.submit();
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
document.addEventListener('DOMContentLoaded', () => {
    Signature.init();
});
export default Signature;