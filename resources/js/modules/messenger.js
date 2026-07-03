const Messenger = {
    unreadCount: 0,

    init() {
        this.bindUnreadListener();
        this.updateUnreadBadge();

        window.toggleMessengerRooms =
            this.toggle.bind(this);

        window.openDocumentChatFromMessenger =
            this.openModalRoom.bind(this);

        window.openMessengerRoom =
            this.openConversation.bind(this);

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

        if (!widget) return;

        if (section === 'messenger') {
            widget.style.display = 'none';

            if (panel) {
                panel.classList.remove('show');
            }

            this.resetUnread();
            return;
        }

        widget.style.display = 'block';

        if (panel) {
            panel.classList.remove('show');
        }
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
                : String(this.unreadCount);
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

    openModalRoom(documentId, title) {
        this.resetUnread();

        document
            .getElementById('messengerRoomsPanel')
            ?.classList
            .remove('show');

        window.Chat.open(documentId, title);
    },

    openConversation(documentId, title, containerId) {
        this.resetUnread();

        window.Chat.open(
            documentId,
            title,
            containerId
        );
    },
};

export default Messenger;