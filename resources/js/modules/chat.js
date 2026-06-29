import { io } from 'socket.io-client';

const Chat = {
    socket: null,
    activeDocumentId: null,

    init() {
        console.log('CHAT INIT');

        this.socket = io('http://localhost:3000', {
            transports: ['polling'],
        });

        this.socket.on('connect', () => {
            console.log('Socket connected:', this.socket.id);

            const documentIds =
                window.messengerDocumentIds || [];

            documentIds.forEach(documentId => {
                this.socket.emit(
                    'join-document-chat',
                    documentId
                );
            });
        });

        this.socket.on('connect_error', (error) => {
            console.error('Socket connection error:', error.message);
        });

        this.socket.on('receive-document-message', (payload) => {

            window.dispatchEvent(
                new CustomEvent('document-chat:new-message', {
                    detail: payload,
                })
            );

            if (
                Number(payload.document_id) !==
                Number(this.activeDocumentId)
            ) {
                return;
            }

            this.appendMessage(payload);
        });

        this.bindEvents();
    },

    bindEvents() {
        const form = document.getElementById('chatForm');
        const input = document.getElementById('chatInput');

        if (!form || !input) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            await this.send(input.value);

            input.value = '';
        });
    },

    async open(documentId, title) {

        this.activeDocumentId = documentId;

        document.getElementById('documentChatModal').style.display = 'flex';
        document.getElementById('chatDocumentTitle').textContent = `Chat - ${title}`;
        document.getElementById('activeDocumentId').value = documentId;
        document.getElementById('chatMessages').innerHTML = '';

        this.socket.emit('join-document-chat', documentId);

        await this.loadMessages(documentId);
    },

    close() {

        document.getElementById('documentChatModal').style.display = 'none';

        this.activeDocumentId = null;
    },

    async loadMessages(documentId) {
        const response = await fetch(`/documents/${documentId}/messages`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            console.error('Failed to load chat messages.');
            return;
        }

        const messages = await response.json();

        messages.forEach((message) => {
            this.appendMessage(message);
        });
    },

    async send(message) {
        message = message.trim();

        if (!message || !this.activeDocumentId) return;

        const response = await fetch(
            `/documents/${this.activeDocumentId}/messages`,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    message,
                }),
            }
        );

        if (!response.ok) {
            console.error('Failed to save chat message.');
            return;
        }

        const savedMessage = await response.json();

        this.socket.emit('send-document-message', savedMessage);
    },

    appendMessage(payload) {

    const messages =
        document.getElementById(
            'chatMessages'
        );

    if (!messages) {
        return;
    }

    const isMine =
        Number(payload.user_id) ===
        Number(window.authUserId);

    messages.insertAdjacentHTML(
        'beforeend',
        `
        <div class="chat-row ${
            isMine
                ? 'chat-row-right'
                : 'chat-row-left'
        }">

            <div class="
                chat-bubble
                ${
                    isMine
                        ? 'chat-bubble-right'
                        : 'chat-bubble-left'
                }
            ">

                <div class="chat-meta">

                    <strong>
                        ${payload.user_name}
                    </strong>

                    <small>
                        ${payload.sent_at ?? ''}
                    </small>

                </div>

                <p>
                    ${this.escapeHtml(
                        payload.message
                    )}
                </p>

            </div>

        </div>
        `
    );

    messages.scrollTop =
        messages.scrollHeight;
},

    getCsrfToken() {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');
    },

    escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    },
};

export default Chat;