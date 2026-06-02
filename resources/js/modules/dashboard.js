const Dashboard = {

    init()
    {
        console.log('DASHBOARD INIT');

        window.showSection =
            this.showSection.bind(this);

        const section =
            new URLSearchParams(
                window.location.search
            ).get('section')
            || 'dashboard';

        this.showSection(section);
    },

    getSectionTitle(section)
    {
        const titles = {
            dashboard: 'Dashboard',
            documents: 'My Documents',
            profile: 'My Profile',
            signed: 'Signed Documents',
            users: 'Role Assignment',
            audit: 'Audit Trail',
            signature: 'My Signature',
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
    }
};

export default Dashboard;