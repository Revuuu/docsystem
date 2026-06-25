const Messenger = {
    init() {
        this.bindSectionWatcher();

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

    toggle() {
        document
            .getElementById('messengerRoomsPanel')
            ?.classList
            .toggle('show');
    },

    openRoom(documentId, title) {
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