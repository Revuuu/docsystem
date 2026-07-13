import { io } from 'socket.io-client';
import ChatService from '../services/chat-service';

const Chat = {
    socket: null,
    activeDocumentId: null,
    messagesContainer: null,

    selectedFile: null,
    previewObjectUrl: null,

    init() {
        console.log('CHAT INIT');

        /*
         * When VITE_CHAT_SOCKET_URL is empty, Socket.IO connects
         * to the current domain. This is recommended when Nginx
         * proxies /socket.io to the Node.js server.
         */
        const socketUrl =
            import.meta.env.VITE_SOCKET_URL?.trim() ||
            undefined;

        console.log(
            'Connecting to Socket.IO:',
            socketUrl ?? window.location.origin
        );

        this.socket = io(socketUrl, {
            transports: ['websocket', 'polling'],
            withCredentials: true,
        });

        this.socket.on('connect', () => {
            console.log(
                'Socket connected:',
                this.socket.id
            );

            const documentIds =
                window.messengerDocumentIds || [];

            documentIds.forEach(documentId => {
                this.socket.emit(
                    'join-document-chat',
                    documentId
                );
            });
        });

        this.socket.on('connect_error', error => {
            console.error(
                'Socket connection error:',
                error.message
            );
        });

        this.socket.on(
            'receive-document-message',
            payload => {
                const message =
                    payload?.data ?? payload;

                console.log(
                    'Received document message:',
                    message
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'document-chat:new-message',
                        {
                            detail: message,
                        }
                    )
                );

                if (
                    Number(message.document_id) !==
                    Number(this.activeDocumentId)
                ) {
                    return;
                }

                this.appendMessage(message);
            }
        );

        this.bindModalForm();
    },

    bindModalForm() {
        const form =
            document.getElementById('chatForm');

        const input =
            document.getElementById('chatInput');

        const fileInput =
            document.getElementById('chatFile');

        const attachButton =
            document.getElementById('chatAttachButton');

        const removeButton =
            document.getElementById(
                'removeChatAttachment'
            );

        if (
            !form ||
            !input ||
            !fileInput ||
            !attachButton
        ) {
            console.error(
                'Required chat form elements were not found.'
            );

            return;
        }

        /*
         * Prevent duplicate event listeners when init()
         * is accidentally called more than once.
         */
        if (form.dataset.chatBound === 'true') {
            return;
        }

        form.dataset.chatBound = 'true';

        attachButton.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            this.handleSelectedFile(fileInput);
        });

        removeButton?.addEventListener('click', () => {
            this.clearSelectedFile();
        });

        form.addEventListener(
            'submit',
            async event => {
                event.preventDefault();

                const sent = await this.send(
                    input.value,
                    this.selectedFile
                );

                /*
                 * Do not clear the form when the upload
                 * or message save fails.
                 */
                if (sent) {
                    input.value = '';
                    this.clearSelectedFile();
                }
            }
        );
    },

    handleSelectedFile(fileInput) {
        const file =
            fileInput.files?.[0] ?? null;

        if (!file) {
            this.clearSelectedFile();
            return;
        }

        const maximumSize =
            10 * 1024 * 1024;

        if (file.size > maximumSize) {
            this.showUploadStatus(
                'The attachment must not exceed 10 MB.',
                true
            );

            this.clearSelectedFile();
            return;
        }

        this.selectedFile = file;

        const preview =
            document.getElementById(
                'chatAttachmentPreview'
            );

        const imagePreview =
            document.getElementById(
                'chatAttachmentImagePreview'
            );

        const attachmentName =
            document.getElementById(
                'chatAttachmentName'
            );

        const attachmentSize =
            document.getElementById(
                'chatAttachmentSize'
            );

        if (attachmentName) {
            attachmentName.textContent =
                file.name;
        }

        if (attachmentSize) {
            attachmentSize.textContent =
                this.formatFileSize(file.size);
        }

        if (preview) {
            preview.hidden = false;
        }

        this.revokePreviewObjectUrl();

        if (
            imagePreview &&
            file.type.startsWith('image/')
        ) {
            this.previewObjectUrl =
                URL.createObjectURL(file);

            imagePreview.src =
                this.previewObjectUrl;

            imagePreview.hidden = false;
        } else if (imagePreview) {
            imagePreview.hidden = true;
            imagePreview.removeAttribute('src');
        }

        this.showUploadStatus('');
    },

    clearSelectedFile() {
        this.revokePreviewObjectUrl();

        this.selectedFile = null;

        const fileInput =
            document.getElementById('chatFile');

        const preview =
            document.getElementById(
                'chatAttachmentPreview'
            );

        const imagePreview =
            document.getElementById(
                'chatAttachmentImagePreview'
            );

        const attachmentName =
            document.getElementById(
                'chatAttachmentName'
            );

        const attachmentSize =
            document.getElementById(
                'chatAttachmentSize'
            );

        if (fileInput) {
            fileInput.value = '';
        }

        if (preview) {
            preview.hidden = true;
        }

        if (imagePreview) {
            imagePreview.hidden = true;
            imagePreview.removeAttribute('src');
        }

        if (attachmentName) {
            attachmentName.textContent = '';
        }

        if (attachmentSize) {
            attachmentSize.textContent = '';
        }
    },

    revokePreviewObjectUrl() {
        if (!this.previewObjectUrl) {
            return;
        }

        URL.revokeObjectURL(
            this.previewObjectUrl
        );

        this.previewObjectUrl = null;
    },

    bindEmbeddedForm(containerId) {
    const form =
        document.getElementById(
            `${containerId}Form`
        );

    const input =
        document.getElementById(
            `${containerId}Input`
        );

    const fileInput =
        document.getElementById(
            `${containerId}File`
        );

    const attachButton =
        document.getElementById(
            `${containerId}AttachButton`
        );

    const removeButton =
        document.getElementById(
            `${containerId}RemoveAttachment`
        );

    const preview =
        document.getElementById(
            `${containerId}AttachmentPreview`
        );

    const attachmentName =
        document.getElementById(
            `${containerId}AttachmentName`
        );

    const status =
        document.getElementById(
            `${containerId}UploadStatus`
        );

    const sendButton =
        document.getElementById(
            `${containerId}SendButton`
        );

    if (
        !form ||
        !input ||
        !fileInput ||
        !attachButton
    ) {
        return;
    }

    let selectedFile = null;

    const clearAttachment = () => {
        selectedFile = null;
        fileInput.value = '';

        if (preview) {
            preview.hidden = true;
        }

        if (attachmentName) {
            attachmentName.textContent = '';
        }
    };

    const showStatus = (
        message,
        isError = false
    ) => {
        if (!status) {
            return;
        }

        status.textContent = message;

        status.classList.toggle(
            'error',
            isError
        );
    };

    attachButton.addEventListener(
        'click',
        () => {
            fileInput.click();
        }
    );

    fileInput.addEventListener(
        'change',
        () => {
            const file =
                fileInput.files?.[0] ?? null;

            if (!file) {
                clearAttachment();
                return;
            }

            const maximumSize =
                10 * 1024 * 1024;

            if (file.size > maximumSize) {
                showStatus(
                    'The attachment must not exceed 10 MB.',
                    true
                );

                clearAttachment();
                return;
            }

            selectedFile = file;

            if (attachmentName) {
                attachmentName.textContent =
                    file.name;
            }

            if (preview) {
                preview.hidden = false;
            }

            showStatus('');
        }
    );

    removeButton?.addEventListener(
        'click',
        () => {
            clearAttachment();
            showStatus('');
        }
    );

    form.addEventListener(
        'submit',
        async event => {
            event.preventDefault();

            const cleanMessage =
                input.value.trim();

            if (!cleanMessage && !selectedFile) {
                showStatus(
                    'Enter a message or attach a file.',
                    true
                );

                return;
            }

            if (sendButton) {
                sendButton.disabled = true;
            }

            attachButton.disabled = true;
            input.disabled = true;

            try {
                const sent = await this.send(
                    cleanMessage,
                    selectedFile
                );

                if (sent) {
                    input.value = '';
                    clearAttachment();
                    showStatus('');
                }
            } finally {
                if (sendButton) {
                    sendButton.disabled = false;
                }

                attachButton.disabled = false;
                input.disabled = false;
                input.focus();
            }
        }
    );
},

    async open(
        documentId,
        title,
        containerId = null
    ) {
        this.activeDocumentId =
            Number(documentId);

        if (containerId) {
            const container =
                document.getElementById(
                    containerId
                );

            if (!container) {
                console.error(
                    'Conversation container not found:',
                    containerId
                );

                return;
            }

            container.classList.add(
    'conversation-panel'
);

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

                    <div id="${containerId}AttachmentPreview"
                        class="chat-attachment-preview"
                        hidden>

                        <span id="${containerId}AttachmentName"></span>

                        <button type="button"
                                id="${containerId}RemoveAttachment"
                                class="remove-chat-attachment"
                                aria-label="Remove attachment">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="chat-input-row">
                        <input type="file"
                            id="${containerId}File"
                            accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,.doc,.docx,.xls,.xlsx,.txt"
                            hidden>
                        
                        <button type="button"
                            id="${containerId}AttachButton"
                            class="chat-attach-btn"
                            title="Attach a file"
                            aria-label="Attach a file">

                        <i class="bi bi-paperclip"></i>
                        </button>

                        <input id="${containerId}Input"
                            type="text"
                            placeholder="Type a message...">

                        <button type="submit"
                            id="${containerId}SendButton"
                            class="chat-send-btn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                    <small id="${containerId}UploadStatus"
                        class="chat-upload-status">
                    </small>
                </form>
            `;

            this.messagesContainer =
                document.getElementById(
                    `${containerId}Messages`
                );

            this.bindEmbeddedForm(containerId);
        } else {
            const modal =
                document.getElementById(
                    'documentChatModal'
                );

            const titleElement =
                document.getElementById(
                    'chatDocumentTitle'
                );

            const activeDocumentInput =
                document.getElementById(
                    'activeDocumentId'
                );

            if (modal) {
                modal.style.display = 'flex';
            }

            if (titleElement) {
                titleElement.textContent =
                    `Chat - ${title}`;
            }

            if (activeDocumentInput) {
                activeDocumentInput.value =
                    documentId;
            }

            this.messagesContainer =
                document.getElementById(
                    'chatMessages'
                );

            if (this.messagesContainer) {
                this.messagesContainer.innerHTML = '';
            }

            this.clearSelectedFile();
            this.showUploadStatus('');
        }

        this.socket.emit(
            'join-document-chat',
            documentId
        );

        await this.loadMessages(documentId);
    },

    close() {
        const modal =
            document.getElementById(
                'documentChatModal'
            );

        if (modal) {
            modal.style.display = 'none';
        }

        this.clearSelectedFile();
        this.showUploadStatus('');

        this.activeDocumentId = null;
    },

    async loadMessages(documentId) {
    try {
        const messages =
            await ChatService.getMessages(
                documentId
            );

        console.log(
            'Loaded document messages:',
            messages
        );

        if (this.messagesContainer) {
            this.messagesContainer.innerHTML = '';
        }

        if (!Array.isArray(messages)) {
            console.error(
                'Chat history response is not an array:',
                messages
            );

            return;
        }

        messages.forEach(message => {
            this.appendMessage(message);
        });
    } catch (error) {
        console.error(
            'Failed to load chat messages.',
            error
        );
    }
},
    async send(message, file = null) {
        const cleanMessage =
            String(message ?? '').trim();

        if (!cleanMessage && !file) {
            this.showUploadStatus(
                'Enter a message or attach a file.',
                true
            );

            return false;
        }

        if (!this.activeDocumentId) {
            this.showUploadStatus(
                'No active document selected.',
                true
            );

            return false;
        }

        this.setSendingState(true);

        try {
    const response =
        await ChatService.sendMessage(
            this.activeDocumentId,
            cleanMessage,
            file
        );

    const savedMessage =
        response?.data ?? response;

    console.log(
        'Saved chat message:',
        savedMessage
    );

    if (!savedMessage?.id) {
        throw new Error(
            'The server did not return the saved message.'
        );
    }

    console.log(
        'Saved attachment:',
        savedMessage.attachment
    );

    /*
     * Display immediately for the sender.
     */
    this.appendMessage(savedMessage);

    /*
     * Broadcast to the other users.
     */
    this.socket.emit(
        'send-document-message',
        savedMessage
    );

    this.showUploadStatus('');

    return true;
} catch (error) {
    console.error(
        'Failed to save chat message:',
        error
    );

    this.showUploadStatus(
        error.response?.data?.message ??
        error.message ??
        'The message could not be sent.',
        true
    );

    return false;
} finally {
    this.setSendingState(false);
}
    },

    appendMessage(rawPayload) {
    const payload =
        rawPayload?.data ?? rawPayload;

    const messages =
        this.messagesContainer;

    if (!messages || !payload) {
        return;
    }

    const messageId =
        payload.id !== undefined &&
        payload.id !== null
            ? String(payload.id)
            : null;

    /*
     * Prevent duplicate display when the message is
     * appended locally and returned through Socket.IO.
     */
    if (
        messageId &&
        messages.querySelector(
            `[data-message-id="${CSS.escape(messageId)}"]`
        )
    ) {
        return;
    }

    const attachment =
        this.normalizeAttachment(
            payload.attachment
        );

    const messageText =
        String(payload.message ?? '').trim();

    if (!messageText && !attachment) {
        return;
    }

    const isMine =
        Number(payload.user_id) ===
        Number(window.authUserId);

    const row =
        document.createElement('div');

    row.className = [
        'chat-row',
        isMine
            ? 'chat-row-right'
            : 'chat-row-left',
    ].join(' ');

    if (messageId) {
        row.dataset.messageId =
            messageId;
    }

    const bubble =
        document.createElement('div');

    bubble.className = [
        'chat-bubble',
        isMine
            ? 'chat-bubble-right'
            : 'chat-bubble-left',
    ].join(' ');

    const meta =
        document.createElement('div');

    meta.className = 'chat-meta';

    const userName =
        document.createElement('strong');

    userName.textContent =
        payload.user_name ??
        'Unknown user';

    const sentAt =
        document.createElement('small');

    sentAt.textContent =
        payload.sent_at ?? '';

    meta.appendChild(userName);
    meta.appendChild(sentAt);
    bubble.appendChild(meta);

    if (messageText !== '') {
        const paragraph =
            document.createElement('p');

        paragraph.textContent =
            messageText;

        bubble.appendChild(paragraph);
    }

    if (attachment) {
        this.appendAttachment(
            bubble,
            attachment
        );
    }

    row.appendChild(bubble);
    messages.appendChild(row);

    messages.scrollTop =
        messages.scrollHeight;
},

    appendAttachment(parent, rawAttachment) {
    const attachment =
        this.normalizeAttachment(
            rawAttachment
        );

    console.log(
        'Displaying attachment:',
        attachment
    );

    if (!attachment) {
        return;
    }

    if (!attachment.url) {
        console.error(
            'Attachment URL is missing:',
            attachment
        );

        const unavailable =
            document.createElement('div');

        unavailable.className =
            'chat-attachment-error';

        unavailable.textContent =
            attachment.name
                ? `Attachment unavailable: ${attachment.name}`
                : 'Attachment unavailable';

        parent.appendChild(unavailable);

        return;
    }

    const container =
        document.createElement('div');

    container.className =
        'chat-message-attachment';

    const mimeType =
        String(
            attachment.mime_type ?? ''
        );

    const isImage =
        attachment.is_image === true ||
        attachment.is_image === 1 ||
        attachment.is_image === '1' ||
        mimeType.startsWith('image/');

    if (isImage) {
        const link =
            document.createElement('a');

        link.href = attachment.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        const image =
            document.createElement('img');

        image.src = attachment.url;

        image.alt =
            attachment.name ??
            'Chat image';

        image.loading = 'lazy';

        image.addEventListener(
            'error',
            () => {
                console.error(
                    'Unable to load attachment image:',
                    attachment.url
                );

                image.hidden = true;

                const fallback =
                    document.createElement('span');

                fallback.textContent =
                    `Open ${attachment.name ?? 'image'}`;

                link.appendChild(fallback);
            }
        );

        link.appendChild(image);
        container.appendChild(link);
    } else {
        const link =
            document.createElement('a');

        link.href = attachment.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        const icon =
            document.createElement('i');

        icon.className =
            'bi bi-paperclip';

        const fileName =
            document.createElement('span');

        fileName.textContent =
            attachment.name ??
            'Open attachment';

        link.appendChild(icon);
        link.appendChild(fileName);

        container.appendChild(link);
    }

    if (attachment.size) {
        const size =
            document.createElement('small');

        size.className =
            'chat-attachment-file-size';

        size.textContent =
            this.formatFileSize(
                Number(attachment.size)
            );

        container.appendChild(size);
    }

    parent.appendChild(container);
},

    normalizeAttachment(attachment) {
        if (!attachment) {
            return null;
        }

        if (typeof attachment === 'object') {
            return attachment;
        }

        try {
            return JSON.parse(attachment);
        } catch (error) {
            console.error(
                'Invalid attachment JSON:',
                attachment,
                error
            );

            return null;
        }
    },

    setSendingState(isSending) {
        const sendButton =
            document.getElementById(
                'chatSendButton'
            );

        const attachButton =
            document.getElementById(
                'chatAttachButton'
            );

        const input =
            document.getElementById(
                'chatInput'
            );

        if (sendButton) {
            sendButton.disabled = isSending;

            sendButton.textContent =
                isSending
                    ? 'Sending...'
                    : 'Send';
        }

        if (attachButton) {
            attachButton.disabled = isSending;
        }

        if (input) {
            input.disabled = isSending;
        }
    },

    showUploadStatus(
        message,
        isError = false
    ) {
        const status =
            document.getElementById(
                'chatUploadStatus'
            );

        if (!status) {
            return;
        }

        status.textContent = message;

        status.classList.toggle(
            'error',
            isError
        );
    },

    formatFileSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        if (bytes < 1024 * 1024) {
            return `${
                (bytes / 1024).toFixed(1)
            } KB`;
        }

        return `${
            (
                bytes /
                1024 /
                1024
            ).toFixed(1)
        } MB`;
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