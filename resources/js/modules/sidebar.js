const Sidebar = {

    init() {
        this.hamburger = document.getElementById('hamburger');
        this.sidebar = document.getElementById('sidebar');
        this.overlay = document.getElementById('overlay');

        if (!this.hamburger || !this.sidebar) return;

        this.bindEvents();
        this.syncMode();
    },

    bindEvents() {
        this.hamburger.addEventListener('click', () => {
            if (window.innerWidth <= 860) {
                this.sidebar.classList.remove('is-hidden');
                this.sidebar.classList.toggle('mobile-open');
                this.overlay?.classList.toggle('show');
            } else {
                this.sidebar.classList.remove('mobile-open');
                this.overlay?.classList.remove('show');
                this.sidebar.classList.toggle('is-hidden');
            }
        });

        this.overlay?.addEventListener('click', () => {
            this.sidebar.classList.remove('mobile-open');
            this.overlay.classList.remove('show');
        });

        window.addEventListener('resize', () => {
            this.syncMode();
        });
    },

    syncMode() {
        if (window.innerWidth <= 860) {
            this.sidebar.classList.remove('is-hidden');
            this.sidebar.classList.remove('mobile-open');
            this.overlay?.classList.remove('show');
        } else {
            this.sidebar.classList.remove('mobile-open');
            this.overlay?.classList.remove('show');
        }
    }
};

export default Sidebar;