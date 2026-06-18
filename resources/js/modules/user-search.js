import debounce
from '../utils/debounce';

import UserService
from '../services/user.service';

const UserSearch = {

    init()
    {
        this.search =
            document.getElementById(
                'userSearch'
            );

        this.container =
            document.getElementById(
                'userTableContainer'
            );

    this.status =
        document.getElementById(
            'statusFilter'
        );


    this.role =
        document.getElementById(
            'roleFilter'
        );

        this.date =
    document.getElementById(
        'dateFilter'
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
        this.role.addEventListener(
            'change',
            () => this.fetch()
        );

        this.status.addEventListener(
            'change',
            () => this.fetch()
        );

         document
        .getElementById('clearFilters')
        ?.addEventListener(
            'click',
            () => this.clearFilters()
        );

        this.date?.addEventListener(
    'change',
    () => this.fetch()
);
    },

    async fetch(page = 1)
    {
           const params = {
        page,
        section: 'user-management',
        search: this.search.value
    };

        const html =
            await UserService.search({

                page,

                section:
                    'user-management',

                search:
                    this.search.value,

                role:
                    this.role.value,

                status:
                    this.status.value,
                
                date: 
                    this.date?.value
            });

        const parser =
            new DOMParser();

        const userDoc =
            parser.parseFromString(
                html,
                'text/html'
            );

        const newTable =
            userDoc.getElementById(
                'userTable'
            );

        if (newTable) {
            document.getElementById('userTable'
            ).innerHTML =
                newTable.innerHTML;

        }
    },
    clearFilters()
{
    this.search.value = '';

    const role =
        document.getElementById('roleFilter');

    const status =
        document.getElementById('statusFilter');

    if (this.date) {
    this.date.value = '';
}

    if (role) role.value = '';
    if (status) status.value = '';

    this.fetch();
},
};

export default UserSearch;