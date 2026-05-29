const Dashboard = {

    init()
    {
        console.log('DASHBOARD INIT');

        window.showSection =
            this.showSection.bind(this);

            console.log(window.showSection);
        const section =
            new URLSearchParams(
                window.location.search
            ).get('section')
            || 'dashboard';

        this.showSection(section);
    },

    showSection(section)
    {
        document
            .querySelectorAll(
                '.dashboard-section'
            )
            .forEach(el => {
                el.style.display = 'none';
            });

        document
            .getElementById(
                `section-${section}`
            )
            ?.style.setProperty(
                'display',
                'block'
            );

        document
            .querySelectorAll('.nav-btn')
            .forEach(btn =>
                btn.classList.remove('active')
            );

        document
            .querySelector(
                `[onclick="showSection('${section}')"]`
            )
            ?.classList.add('active');
    }
};

export default Dashboard;