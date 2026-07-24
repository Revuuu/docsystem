const PurchaseOrderQueue = {
    init() {
        this.root = document.getElementById(
            'purchaseOrderQueue'
        );

        if (!this.root) {
            return;
        }

        this.indexUrl =
            this.root.dataset.indexUrl;

        this.forwardUrlTemplate =
            this.root.dataset.forwardUrlTemplate;

        this.csrfToken =
            this.root.dataset.csrfToken;

        this.body = document.getElementById(
            'purchaseOrderTableBody'
        );

        this.search = document.getElementById(
            'purchaseOrderSearchInput'
        );

        this.refreshButton =
            document.getElementById(
                'purchaseOrderRefreshButton'
            );

        this.resultsInfo =
            document.getElementById(
                'purchaseOrderResultsInfo'
            );

        this.pagination =
            document.getElementById(
                'purchaseOrderPagination'
            );

        this.alert = document.getElementById(
            'purchaseOrderQueueAlert'
        );

        this.currentPage = 1;
        this.searchTimeout = null;
        this.abortController = null;

        this.bindEvents();
        this.fetch();
    },

    bindEvents() {
        this.search?.addEventListener(
            'input',
            () => {
                window.clearTimeout(
                    this.searchTimeout
                );

                this.searchTimeout =
                    window.setTimeout(() => {
                        this.currentPage = 1;
                        this.fetch();
                    }, 350);
            }
        );

        this.refreshButton?.addEventListener(
            'click',
            () => {
                this.fetch();
            }
        );

        this.body?.addEventListener(
            'click',
            async event => {
                const menuButton =
                    event.target.closest(
                        '[data-po-menu-toggle]'
                    );

                if (menuButton) {
                    if (
                        typeof window.toggleMoreMenu ===
                        'function'
                    ) {
                        window.toggleMoreMenu(
                            event,
                            menuButton
                        );
                    } else {
                        menuButton
                            .closest('.more-menu')
                            ?.classList.toggle('open');
                    }

                    return;
                }

                const forwardButton =
                    event.target.closest(
                        '[data-po-forward]'
                    );

                if (forwardButton) {
                    await this.forward(
                        forwardButton
                    );

                    return;
                }

                const viewButton =
                    event.target.closest(
                        '[data-po-view-url]'
                    );

                if (viewButton) {
                    window.openPdfModal?.(
                        viewButton.dataset.poViewUrl
                    );
                }
            }
        );

        this.pagination?.addEventListener(
            'click',
            event => {
                const button =
                    event.target.closest(
                        '[data-po-page]'
                    );

                if (
                    !button ||
                    button.disabled
                ) {
                    return;
                }

                this.currentPage = Number(
                    button.dataset.poPage
                );

                this.fetch();
            }
        );
    },

    async fetch({
        preserveAlert = false
    } = {}) {
        this.abortController?.abort();

        this.abortController =
            new AbortController();

        this.setLoading();

        if (!preserveAlert) {
            this.clearAlert();
        }

        const url = new URL(
            this.indexUrl,
            window.location.origin
        );

        url.searchParams.set(
            'page',
            this.currentPage
        );

        const search =
            this.search?.value.trim();

        if (search) {
            url.searchParams.set(
                'search',
                search
            );
        }

        try {
            const response =
                await window.fetch(
                    url,
                    {
                        headers: {
                            Accept:
                                'application/json',
                        },

                        credentials:
                            'same-origin',

                        signal:
                            this.abortController
                                .signal,
                    }
                );

            const result =
                await this.readJson(response);

            if (!response.ok) {
                throw new Error(
                    result.message ??
                    'Unable to load Purchase Orders.'
                );
            }

            this.renderRows(
                result.data ?? []
            );

            this.renderMeta(
                result.meta ?? {}
            );
        } catch (error) {
            if (
                error.name ===
                'AbortError'
            ) {
                return;
            }

            console.error(error);

            this.showAlert(
                error.message,
                'danger'
            );

            this.body.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center py-4"
                    >
                        Purchase Orders could not be loaded.
                    </td>
                </tr>
            `;
        }
    },

    async forward(button) {
        const poNo =
            button.dataset.poForward;

        const confirmed =
            window.confirm(
                `Forward Purchase Order ${poNo} ` +
                'to its configured approvers?'
            );

        if (!confirmed) {
            return;
        }

        const originalText =
            button.textContent;

        button.disabled = true;
        button.textContent =
            'Forwarding...';

        this.clearAlert();

        const url =
            this.forwardUrlTemplate.replace(
                '__PO_NUMBER__',
                encodeURIComponent(poNo)
            );

        try {
            const response =
                await window.fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {
                            Accept:
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                this.csrfToken,
                        },

                        credentials:
                            'same-origin',

                        body:
                            JSON.stringify({}),
                    }
                );

            const result =
                await this.readJson(response);

            if (!response.ok) {
                throw new Error(
                    result.message ??
                    'The Purchase Order could not be forwarded.'
                );
            }

            this.showAlert(
                result.message,
                'success'
            );

            await this.fetch({
                preserveAlert: true
            });
        } catch (error) {
            console.error(error);

            this.showAlert(
                error.message,
                'danger'
            );

            button.disabled = false;
            button.textContent =
                originalText;
        }
    },

    renderRows(rows) {
        if (rows.length === 0) {
            this.body.innerHTML = `
                <tr>
                    <td
                        colspan="9"
                        class="text-center py-4"
                    >
                        No Purchase Orders found.
                    </td>
                </tr>
            `;

            return;
        }

        this.body.innerHTML = rows
            .map(
                row =>
                    this.renderRow(row)
            )
            .join('');
    },

    renderRow(row) {
        const workflow =
            row.workflow ?? {};

        const progress = Math.min(
            100,
            Math.max(
                0,
                Number(
                    workflow.progress ?? 0
                )
            )
        );

        return `
            <tr
                data-po-number="${this.escape(
                    row.po_no
                )}"
            >
                <td>
                    <div class="file-name">
                        PO ${this.escape(
                            row.po_no
                        )}
                    </div>
                </td>

                <td>
                    ${this.escape(
                        row.supplier ??
                        'Unknown supplier'
                    )}
                </td>

                <td>
                    ${this.escape(
                        row.po_date ?? '-'
                    )}
                </td>

                <td>
                    ${Number(
                        row.item_count ?? 0
                    )}
                </td>

                <td>
                    ₱${this.money(
                        row.total_amount
                    )}
                </td>

                <td>
                    <span
                        class="status-pill
                            ${this.statusClass(
                                workflow.status_key
                            )}"
                    >
                        ${this.escape(
                            workflow.status ?? '-'
                        )}
                    </span>
                </td>

                <td>
                    <div
                        class="progress-container"
                    >
                        <div
                            class="progress-track"
                        >
                            <div
                                class="progress-fill"
                                style="
                                    width:
                                    ${progress}%;
                                "
                            ></div>
                        </div>

                        <small>
                            ${progress}%
                        </small>
                    </div>
                </td>

                <td>
                    ${this.escape(
                        workflow
                            .current_signatory ??
                        '-'
                    )}

                    ${
                        workflow
                            .current_signatory_role
                            ? `
                                <br>

                                <small>
                                    ${this.escape(
                                        workflow
                                            .current_signatory_role
                                    )}
                                </small>
                            `
                            : ''
                    }
                </td>

                <td>
                    <div class="more-menu">
                        <button
                            type="button"
                            class="
                                table-action-btn
                                btn-clear
                            "
                            data-po-menu-toggle
                            aria-label="
                                Open PO actions
                            "
                        >
                            <i
                                class="
                                    bi
                                    bi-three-dots-vertical
                                "
                            ></i>
                        </button>

                        <div
                            class="
                                more-menu-dropdown
                            "
                        >
                            ${this.actionItems(
                                row
                            )}
                        </div>
                    </div>
                </td>
            </tr>
        `;
    },

    actionItems(row) {
        const workflow =
            row.workflow ?? {};

        if (!workflow.forwarded) {
            return `
                <button
                    type="button"
                    data-po-forward="${this.escape(
                        row.po_no
                    )}"
                >
                    Forward for Approval
                </button>
            `;
        }

        const actions = [];

        if (workflow.view_url) {
            actions.push(`
                <button
                    type="button"
                    data-po-view-url="${this.escapeAttribute(
                        workflow.view_url
                    )}"
                >
                    View PDF
                </button>
            `);
        }

        if (workflow.download_url) {
            actions.push(`
                <a
                    href="${this.escapeAttribute(
                        workflow.download_url
                    )}"
                >
                    Download PDF
                </a>
            `);
        }

        actions.push(`
            <button
                type="button"
                disabled
            >
                Already Forwarded
            </button>
        `);

        return actions.join('');
    },

    renderMeta(meta) {
        const total =
            Number(meta.total ?? 0);

        const current =
            Number(
                meta.current_page ?? 1
            );

        const last =
            Number(meta.last_page ?? 1);

        this.resultsInfo.textContent =
            `${total} Purchase Order` +
            `${total === 1 ? '' : 's'} found`;

        if (last <= 1) {
            this.pagination.innerHTML =
                '';

            return;
        }

        const buttons = [];

        for (
            let page = 1;
            page <= last;
            page++
        ) {
            buttons.push(`
                <button
                    type="button"
                    data-po-page="${page}"
                    ${
                        page === current
                            ? 'disabled'
                            : ''
                    }
                >
                    ${page}
                </button>
            `);
        }

        this.pagination.innerHTML =
            buttons.join('');
    },

    setLoading() {
        this.body.innerHTML = `
            <tr>
                <td
                    colspan="9"
                    class="text-center py-4"
                >
                    Loading Purchase Orders...
                </td>
            </tr>
        `;

        this.resultsInfo.textContent =
            'Loading Purchase Orders...';
    },

    showAlert(message, type) {
        if (!this.alert) {
            return;
        }

        this.alert.className =
            `alert alert-${type}`;

        this.alert.textContent =
            message;

        this.alert.hidden = false;
    },

    clearAlert() {
        if (!this.alert) {
            return;
        }

        this.alert.hidden = true;
        this.alert.textContent = '';
    },

    statusClass(status) {
        return {
            approved: 'approved',
            rejected: 'rejected',
            pending: 'pending',
            waiting: 'pending',
            in_progress: 'pending',
            not_forwarded: 'ongoing',
        }[status] ?? 'pending';
    },

    money(value) {
        return new Intl.NumberFormat(
            'en-PH',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ).format(
            Number(value ?? 0)
        );
    },

    async readJson(response) {
        const contentType =
            response.headers.get(
                'content-type'
            );

        if (
            !contentType?.includes(
                'application/json'
            )
        ) {
            return {};
        }

        return response.json();
    },

    escape(value) {
        const element =
            document.createElement('div');

        element.textContent =
            String(value ?? '');

        return element.innerHTML;
    },

    escapeAttribute(value) {
        return this.escape(value)
            .replaceAll(
                '"',
                '&quot;'
            )
            .replaceAll(
                "'",
                '&#039;'
            );
    },
};

document.addEventListener(
    'DOMContentLoaded',
    () => {
        PurchaseOrderQueue.init();
    }
);

export default PurchaseOrderQueue;