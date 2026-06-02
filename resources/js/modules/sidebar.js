const Sidebar = {

    init() {

        this.hamburger = document.getElementById('hamburger');
        this.sidebar = document.getElementById('sidebar');
        this.overlay = document.getElementById('overlay');

        if (!this.hamburger || !this.sidebar) return;

        this.bindEvents();
    },

    bindEvents() {

        this.hamburger.addEventListener('click', () => {

            // Mobile
            if (window.innerWidth <= 860) {

                this.sidebar.classList.toggle('mobile-open');
                this.overlay?.classList.toggle('show');

            }
            // Desktop
            else {

                this.sidebar.classList.toggle('is-hidden');

            }

        });

        this.overlay?.addEventListener('click', () => {

            this.sidebar.classList.remove('mobile-open');
            this.overlay.classList.remove('show');

        });

        window.addEventListener('resize', () => {

            // Cleanup when switching between desktop/mobile
            if (window.innerWidth > 860) {

                this.sidebar.classList.remove('mobile-open');
                this.overlay?.classList.remove('show');

            }

        });

    }
};

export default Sidebar;