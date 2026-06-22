import debounce from '../utils/debounce';

import UserService from '../services/user.service';

const UserSearch = {
    init()
    {
        this.search =
            document.getElementById('userSearch');

        this.container =
            document.getElementById('userTableContainer');

        this.status =
            document.getElementById('statusFilter');

        this.role =
            document.getElementById('roleFilter');

        this.date =
            document.getElementById('dateFilter');

        this.resultsInfo =
            document.getElementById('userResultsInfo');

        if (!this.search) return;

        this.bindEvents();
    },

    bindEvents()
    {
        this.search.addEventListener(
            'input',
            debounce(() => this.fetch())
        );

        this.role?.addEventListener(
            'change',
            () => this.fetch()
        );

        this.status?.addEventListener(
            'change',
            () => this.fetch()
        );

        this.date?.addEventListener(
            'change',
            () => this.fetch()
        );

        document
            .getElementById('clearFilters')
            ?.addEventListener(
                'click',
                () => this.clearFilters()
            );
    },

    async fetch(page = 1)
    {
        const html =
            await UserService.search({
                page,
                section: 'user-management',
                search: this.search.value,
                role: this.role?.value,
                status: this.status?.value,
                date: this.date?.value
            });

        const parser =
            new DOMParser();

        const userDoc =
            parser.parseFromString(
                html,
                'text/html'
            );

        const newTable =
            userDoc.getElementById('userTable');

        const currentTable =
            document.getElementById('userTable');

        if (newTable && currentTable) {
            currentTable.innerHTML =
                newTable.innerHTML;
        }

        this.updateVisibleCount();
    },

    updateVisibleCount()
    {
        const rows =
            document.querySelectorAll('#userTable tr');

        const filteredCount =
            [...rows].filter(row => {
                const cells =
                    row.querySelectorAll('td');

                return cells.length > 1;
            }).length;

        const info =
            document.getElementById('userResultsInfo');

        if (!info) return;

        const total =
            info.dataset.total || filteredCount;

        info.textContent =
            `Showing ${filteredCount} of ${total} users`;
    },

    clearFilters()
    {
        this.search.value = '';

        if (this.role) {
            this.role.value = '';
        }

        if (this.status) {
            this.status.value = '';
        }

        if (this.date) {
            this.date.value = '';
        }

        this.fetch();
    },
};

export default UserSearch;