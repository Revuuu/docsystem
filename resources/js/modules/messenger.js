const Messenger = {
    unreadCount: 0,
    init() {
        this.bindSectionWatcher();
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

            if (
                !panel ||
                panel.contains(event.target) ||
                button?.contains(event.target)
            ) {
                return;
            }

            panel.classList.remove('show');
        });
    },

    bindSectionWatcher() {
        const widget =
            document.getElementById('messengerWidget');

        if (!widget) {
            return;
        }

        const updateVisibility = () => {
            const documentsSection =
                document.getElementById('section-documents');

            const isVisible =
                documentsSection &&
                getComputedStyle(documentsSection).display !== 'none';

            widget.style.display =
                isVisible
                    ? 'block'
                    : 'none';
        };

        updateVisibility();

        const observer =
            new MutationObserver(updateVisibility);

        observer.observe(
            document.body,
            {
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class'],
            }
        );
    },

    bindUnreadListener() {

        window.addEventListener(
            'document-chat:new-message',
            (event) => {

                const payload =
                    event.detail;

                console.log(
                    'Unread message:',
                    payload
                );

                if (
                    Number(payload.user_id) ===
                    Number(window.authUserId)
                ) {
                    return;
                }

                const modal =
                    document.getElementById(
                        'documentChatModal'
                    );

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
            document.getElementById(
                'messengerUnreadBadge'
            );

        if (!badge) {
            return;
        }

        if (this.unreadCount <= 0) {

            badge.style.display =
                'none';

            return;
        }

        badge.style.display =
            'flex';

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