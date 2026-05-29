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

        if (!this.search) return;

        this.bindEvents();
    },

    bindEvents()
    {
        this.search.addEventListener(
            'input',
            debounce(
                () => this.fetch()
            )
        );

        this.status.addEventListener(
            'change',
            () => this.fetch()
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
                    this.status.value
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
    }
};

export default DocumentSearch;