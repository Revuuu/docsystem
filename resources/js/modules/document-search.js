import debounce
from '../utils/debounce';

import DocumentService
from '../services/document.service';

const DocumentSearch = {

    init()
    {
        this.search =
            document.getElementById(
                'documentSearchInput'
            );

        this.status =
            document.getElementById(
                'documentStatusFilter'
            );

        this.container =
            document.getElementById(
                'documentsTableContainer'
            );

        this.clear =
            document.getElementById(
                'documentClearFilters'
            );

        if (!this.search || !this.container) return;

        this.isWorkflow =
            !!document.getElementById(
                'section-workflow'
            );

        this.bindEvents();
        this.updateVisibleCount();
    },

    bindEvents()
    {
        this.search.addEventListener(
            'input',
            debounce(() => {
                if (this.isWorkflow) {
                    this.filterWorkflowRows();
                    return;
                }

                this.fetch();
            })
        );

        this.status?.addEventListener(
            'change',
            () => {
                if (this.isWorkflow) {
                    this.filterWorkflowRows();
                    return;
                }

                this.fetch();
            }
        );

        this.clear?.addEventListener(
            'click',
            () => {
                this.search.value = '';

                if (this.status) {
                    this.status.value = '';
                }

                if (this.isWorkflow) {
                    this.filterWorkflowRows();
                    return;
                }

                this.fetch();
            }
        );
    },

    async fetch(page = 1)
    {
        const html =
            await DocumentService.search({

                page,

                section:
                    'documents',

                search:
                    this.search.value,

                status:
                    this.status?.value || ''
            });

        const parser =
            new DOMParser();

        const doc =
            parser.parseFromString(
                html,
                'text/html'
            );

        const newContainer =
            doc.getElementById(
                'documentsTableContainer'
            );

        if (newContainer) {
            this.container.innerHTML =
                newContainer.innerHTML;
        }

        this.updateVisibleCount();
    },

    filterWorkflowRows()
    {
        const searchValue =
            this.search.value
                .trim()
                .toLowerCase();

        const statusValue =
            this.status?.value || '';

        const rows =
            document.querySelectorAll(
                '#documentsTableContainer .document-row'
            );

        rows.forEach(row => {
            const title =
                row.dataset.title || '';

            const status =
                row.dataset.status || '';

            const matchesSearch =
                !searchValue ||
                title.includes(searchValue);

            const matchesStatus =
                !statusValue ||
                status === statusValue;

            row.style.display =
                matchesSearch && matchesStatus
                    ? ''
                    : 'none';
        });

        this.updateVisibleCount();
        this.toggleNoResults();
    },

    updateVisibleCount()
    {
        const rows =
            document.querySelectorAll(
                '#documentsTableContainer .document-row'
            );

        const visibleCount =
            [...rows].filter(row => {
                return row.style.display !== 'none';
            }).length;

        const info =
            document.getElementById(
                'docCountResultsInfo'
            );

        if (!info) return;

        const total =
            info.dataset.total || rows.length;

        info.textContent =
            `Showing ${visibleCount} of ${total} documents`;
    },

    toggleNoResults()
    {
        const rows =
            document.querySelectorAll(
                '#documentsTableContainer .document-row'
            );

        const noResults =
            document.getElementById(
                'noResultsMessage'
            );

        if (!noResults) return;

        const visibleCount =
            [...rows].filter(row => {
                return row.style.display !== 'none';
            }).length;

        noResults.style.display =
            visibleCount === 0
                ? 'block'
                : 'none';
    },
};

export default DocumentSearch;