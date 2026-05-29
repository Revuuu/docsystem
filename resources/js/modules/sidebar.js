const Sidebar = {

    init()
    {
        this.hamburger =
            document.getElementById('hamburger');

        this.sidebar =
            document.getElementById('sidebar');

        this.overlay =
            document.getElementById('overlay');

        if (!this.hamburger) return;

        this.bindEvents();
    },

    bindEvents()
    {
        this.hamburger
            ?.addEventListener(
                'click',
                () => this.toggle()
            );

        this.overlay
            ?.addEventListener(
                'click',
                () => this.close()
            );
    },

    open()
    {
        this.sidebar?.classList.add('open');
        this.overlay?.classList.add('show');
    },

    close()
    {
        this.sidebar?.classList.remove('open');
        this.overlay?.classList.remove('show');
    },

    toggle()
    {
        this.sidebar?.classList.contains('open')
            ? this.close()
            : this.open();
    }
};

export default Sidebar;