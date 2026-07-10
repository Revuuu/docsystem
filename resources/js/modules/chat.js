import { io } from 'socket.io-client';
import ChatService from '../services/chat-service';

const Chat = {
    socket: null,
    activeDocumentId: null,
    messagesContainer: null,

    init() {
        console.log('CHAT INIT');

        const socketUrl =
            import.meta.env.VITE_SOCKET_URL ||
            `${window.location.protocol}//${window.location.hostname}:3000`;

        console.log('Connecting to Socket.IO:', socketUrl);

        this.socket = io(socketUrl, {
            transports: ['websocket', 'polling'],
            withCredentials: true,
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

        this.bindModalForm();
    },

    bindModalForm() {
        const form =
            document.getElementById('chatForm');

        const input =
            document.getElementById('chatInput');

        if (!form || !input) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            await this.send(input.value);

            input.value = '';
        });
    },

    bindEmbeddedForm(containerId) {
        const form =
            document.getElementById(`${containerId}Form`);

        const input =
            document.getElementById(`${containerId}Input`);

        if (!form || !input) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            await this.send(input.value);

            input.value = '';
        });
    },

    async open(documentId, title, containerId = null) {
        this.activeDocumentId = documentId;

        if (containerId) {
            const container =
                document.getElementById(containerId);

            if (!container) {
                console.error(
                    'Conversation container not found:',
                    containerId
                );
                return;
            }

            container.innerHTML = `
                <div class="chat-header mobile-chat-header">

                    <button type="button"
                            class="mobile-chat-back"
                            onclick="backToMessengerRooms()">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div>
                        <h3>${this.escapeHtml(title)}</h3>
                        <small>Document conversation</small>
                    </div>

                </div>

                <div id="${containerId}Messages"
                    class="chat-messages">
                </div>

                <form id="${containerId}Form"
                    class="chat-form">

                    <input id="${containerId}Input"
                        type="text"
                        placeholder="Type a message...">

                    <button type="submit">
                        <i class="bi bi-send-fill"></i>
                    </button>

                </form>
            `;

            this.messagesContainer =
                document.getElementById(`${containerId}Messages`);

            this.bindEmbeddedForm(containerId);
        } else {
            document.getElementById('documentChatModal').style.display = 'flex';
            document.getElementById('chatDocumentTitle').textContent = `Chat - ${title}`;
            document.getElementById('activeDocumentId').value = documentId;

            this.messagesContainer =
                document.getElementById('chatMessages');

            this.messagesContainer.innerHTML = '';
        }

        this.socket.emit('join-document-chat', documentId);

        await this.loadMessages(documentId);
    },

    close() {
        document.getElementById('documentChatModal').style.display = 'none';

        this.activeDocumentId = null;
    },

    async loadMessages(documentId) {
        try {
            const messages =
                await ChatService.getMessages(documentId);

            if (this.messagesContainer) {
                this.messagesContainer.innerHTML = '';
            }

            messages.forEach((message) => {
                this.appendMessage(message);
            });

        } catch (error) {
            console.error('Failed to load chat messages.', error);
        }
    },

    async send(message) {
        message = message.trim();

        if (!message || !this.activeDocumentId) return;

        try {
            const savedMessage =
                await ChatService.sendMessage(
                    this.activeDocumentId,
                    message
                );

            this.socket.emit(
                'send-document-message',
                savedMessage
            );

        } catch (error) {
            console.error('Failed to save chat message.', error);
        }
    },

    appendMessage(payload) {
        const messages =
            this.messagesContainer;

        if (!messages) return;

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
                <div class="chat-bubble ${
                    isMine
                        ? 'chat-bubble-right'
                        : 'chat-bubble-left'
                }">
                    <div class="chat-meta">
                        <strong>${this.escapeHtml(payload.user_name)}</strong>
                        <small>${payload.sent_at ?? ''}</small>
                    </div>

                    <p>${this.escapeHtml(payload.message)}</p>
                  
                </div>
            </div>
            `
        );

        messages.scrollTop =
            messages.scrollHeight;
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