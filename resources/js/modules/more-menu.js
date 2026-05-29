const MoreMenu = {

    init() {

        document.addEventListener('click', () => {

            document
                .querySelectorAll('.more-menu.open')
                .forEach(menu => {
                    menu.classList.remove('open');
                });

        });

        window.toggleMoreMenu = this.toggle.bind(this);
    },

    toggle(event, button) {

        event.stopPropagation();

        const currentMenu =
            button.closest('.more-menu');

        document
            .querySelectorAll('.more-menu.open')
            .forEach(menu => {

                if (menu !== currentMenu) {
                    menu.classList.remove('open');
                }

            });

        currentMenu.classList.toggle('open');
    }
};

export default MoreMenu;