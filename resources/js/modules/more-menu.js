const MoreMenu = {
    init() {
        window.toggleMoreMenu = this.toggle.bind(this);

        document.addEventListener('click', () => {
            this.closeAll();
        });

        window.addEventListener('resize', () => {
            this.closeAll();
        });

        window.addEventListener('scroll', () => {
            this.closeAll();
        }, true);
    },

    toggle(event, button) {
        event.preventDefault();
        event.stopPropagation();

        const currentMenu = button.closest('.more-menu');
        if (!currentMenu) return;

        const willOpen = !currentMenu.classList.contains('open');

        this.closeAll(currentMenu);

        if (!willOpen) {
            this.closeMenu(currentMenu);
            return;
        }

        currentMenu.classList.add('open');
        this.positionDropdown(currentMenu, button);
    },

    closeAll(exceptMenu = null) {
        document
            .querySelectorAll('.more-menu.open')
            .forEach(menu => {
                if (menu !== exceptMenu) {
                    this.closeMenu(menu);
                }
            });
    },

    closeMenu(menu) {
        const dropdown = menu.querySelector('.more-dropdown');

        menu.classList.remove('open', 'drop-up');

        if (dropdown) {
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.right = '';
        }
    },

    positionDropdown(menu, button) {
        const dropdown = menu.querySelector('.more-dropdown');
        if (!dropdown) return;

        dropdown.style.visibility = 'hidden';
        dropdown.style.display = 'block';

        const dropdownWidth = dropdown.offsetWidth;
        const dropdownHeight = dropdown.offsetHeight;
        const buttonRect = button.getBoundingClientRect();

        const gap = 8;
        const padding = 16;

        const spaceBelow = window.innerHeight - buttonRect.bottom;
        const spaceAbove = buttonRect.top;

        let top;

        if (spaceBelow < dropdownHeight + gap && spaceAbove > dropdownHeight + gap) {
            top = buttonRect.top - dropdownHeight - gap;
            menu.classList.add('drop-up');
        } else {
            top = buttonRect.bottom + gap;
            menu.classList.remove('drop-up');
        }

        let left = buttonRect.right - dropdownWidth;

        left = Math.max(padding, left);
        left = Math.min(left, window.innerWidth - dropdownWidth - padding);

        dropdown.style.top = `${top}px`;
        dropdown.style.left = `${left}px`;
        dropdown.style.right = 'auto';

        dropdown.style.display = '';
        dropdown.style.visibility = '';
    }
};

export default MoreMenu;