const Dashboard = {

    init()
    {
        console.log('DASHBOARD INIT');
        window.editUser =
            this.editUser.bind(this);

        window.closeEditUserModal =
            this.closeEditUserModal.bind(this);

        window.showSection =
            this.showSection.bind(this);

        this.initTopbarUserDropdown();

         const urlSection = new URLSearchParams(
                window.location.search
            ).get('section');

            const defaultSection =
                document.getElementById('section-admin-dashboard')
                    ? 'admin-dashboard'
                    : 'dashboard';

        this.showSection(urlSection || defaultSection);
    },

    getSectionTitle(section)
    {
        const titles = {
            dashboard: 'Dashboard',
            documents: 'My Documents',
            profile: 'My Profile',
            signed: 'Signed Documents',
            workflow: 'Document Workflow',
            users: 'Role Assignment',
            audit: 'Audit Trail',
            signature: 'My Signature',
            messenger: 'Messenger',
            templates: 'Templates',
            'admin-dashboard': 'Admin Dashboard',
            'user-management': 'User Management',
        };

        return titles[section] ?? 'Dashboard';
    },

    updateBanner(section)
    {
        const bannerTitle =
            document.getElementById('bannerTitle');

        if (!bannerTitle) return;

        bannerTitle.textContent =
            this.getSectionTitle(section);
    },

    showSection(section)
    {
        document
            .querySelectorAll('.dashboard-section')
            .forEach(el => {
                el.style.display = 'none';
            });

        document
            .getElementById(`section-${section}`)
            ?.style.setProperty('display', 'block');

        document
            .querySelectorAll('.nav-btn')
            .forEach(btn =>
                btn.classList.remove('active')
            );

        document
            .querySelector(`[onclick="showSection('${section}')"]`)
            ?.classList.add('active');

        this.updateBanner(section);

        window.Messenger?.onSectionChanged(section);
    },
    
    editUser(id, name, email, role)
    {
    document.getElementById(
        'edit_name'
    ).value = name;

    document.getElementById(
        'edit_email'
    ).value = email;

    document.getElementById(
        'edit_role'
    ).value = role;

    document.getElementById(
        'editUserForm'
    ).action = `/users/${id}`;

    document.getElementById(
        'editUserModal'
    ).style.display = 'flex';
},

closeEditUserModal()
{
    document.getElementById(
        'editUserModal'
    ).style.display = 'none';
},

initTopbarUserDropdown()
{
    this.topbarUserDropdown =
        document.getElementById(
            'topbarUserDropdown'
        );

    this.topbarUserTrigger =
        document.getElementById(
            'topbarUserTrigger'
        );

    this.topbarUserMenu =
        document.getElementById(
            'topbarUserMenu'
        );

    if (
        !this.topbarUserDropdown ||
        !this.topbarUserTrigger ||
        !this.topbarUserMenu
    ) {
        return;
    }

    this.topbarUserTrigger.addEventListener(
        'click',
        (event) => {
            event.stopPropagation();

            const isOpen =
                this.topbarUserDropdown
                    .classList
                    .toggle('is-open');

            this.topbarUserTrigger.setAttribute(
                'aria-expanded',
                String(isOpen)
            );
        }
    );

    this.topbarUserMenu.addEventListener(
        'click',
        (event) => {
            event.stopPropagation();
        }
    );

    document.addEventListener(
        'click',
        () => {
            this.closeTopbarUserDropdown();
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (event.key === 'Escape') {
                this.closeTopbarUserDropdown();
            }
        }
    );
},

closeTopbarUserDropdown()
{
    if (
        !this.topbarUserDropdown ||
        !this.topbarUserTrigger
    ) {
        return;
    }

    this.topbarUserDropdown.classList.remove(
        'is-open'
    );

    this.topbarUserTrigger.setAttribute(
        'aria-expanded',
        'false'
    );
},
};

export default Dashboard;