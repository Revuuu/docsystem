const Messenger = {
    unreadCount: 0,

    init() {
        this.bindUnreadListener();
        this.updateUnreadBadge();

        window.toggleMessengerRooms =
            this.toggle.bind(this);

        window.openDocumentChatFromMessenger =
            this.openRoom.bind(this);

        document.addEventListener('click', (event) => {
            const panel =
                document.getElementById('messengerRoomsPanel');

            const button =
                document.querySelector('.messenger-float-btn');

            const messengerSection =
                document.getElementById('section-messenger');

            const messengerActive =
                messengerSection &&
                getComputedStyle(messengerSection).display !== 'none';

            if (
                messengerActive ||
                !panel ||
                panel.contains(event.target) ||
                button?.contains(event.target)
            ) {
                return;
            }

            panel.classList.remove('show');
        });
    },

    onSectionChanged(section) {

    const widget =
        document.getElementById('messengerWidget');

    const panel =
        document.getElementById('messengerRoomsPanel');

    const floatButton =
        document.querySelector('.messenger-float-btn');

    if (!widget || !panel || !floatButton) {
        return;
    }

    if (section === 'messenger') {

        // Hide floating widget completely
        widget.style.display = 'none';

        // Show the room list inside the Messenger page
        panel.classList.add('show');

        this.resetUnread();

        return;
    }

    // Show floating widget on other pages
    widget.style.display = 'block';

    floatButton.style.display = 'flex';

    panel.classList.remove('show');
},

    bindUnreadListener() {
        window.addEventListener(
            'document-chat:new-message',
            (event) => {
                const payload =
                    event.detail;

                if (
                    Number(payload.user_id) ===
                    Number(window.authUserId)
                ) {
                    return;
                }

                const modal =
                    document.getElementById('documentChatModal');

                const chatOpen =
                    modal &&
                    modal.style.display === 'flex';

                if (
                    chatOpen &&
                    Number(window.Chat?.activeDocumentId) ===
                    Number(payload.document_id)
                ) {
                    return;
                }

                this.unreadCount++;
                this.updateUnreadBadge();
            }
        );
    },

    updateUnreadBadge() {
        const badge =
            document.getElementById('messengerUnreadBadge');

        if (!badge) return;

        if (this.unreadCount <= 0) {
            badge.style.display = 'none';
            badge.textContent = '0';
            return;
        }

        badge.style.display = 'flex';
        badge.textContent =
            this.unreadCount > 99
                ? '99+'
                : this.unreadCount;
    },

    resetUnread() {
        this.unreadCount = 0;
        this.updateUnreadBadge();
    },

    toggle() {
        this.resetUnread();

        document
            .getElementById('messengerRoomsPanel')
            ?.classList
            .toggle('show');
    },

    openRoom(documentId, title) {
        this.resetUnread();

        document
            .getElementById('messengerRoomsPanel')
            ?.classList
            .remove('show');

        if (!window.Chat) {
            console.error('Chat module is not available.');
            return;
        }

        window.Chat.open(documentId, title);
    },
};

export default Messenger;