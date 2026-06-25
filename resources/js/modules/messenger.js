const Messenger = {
    init() {
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