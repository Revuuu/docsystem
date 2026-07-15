import { io } from 'socket.io-client';
import ChatService from '../services/chat-service';
import Viewer from 'viewerjs';
import 'viewerjs/dist/viewer.css';

const Chat = {
    socket: null,
    activeDocumentId: null,
    messagesContainer: null,

    selectedFiles: [],
    maxAttachments: 3,
    maxAttachmentBytes: 10 * 1024 * 1024,
    imageViewers: [],
    
    init() {
        console.log('CHAT INIT');

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
            document.getElementById(
                'chatAttachButton'
            );

        const preview =
            document.getElementById(
                'chatAttachmentPreview'
            );

        const attachmentList =
            document.getElementById(
                'chatAttachmentList'
            );

        if (
            !form ||
            !input ||
            !fileInput ||
            !attachButton ||
            !preview ||
            !attachmentList
        ) {
            console.error(
                'Required chat form elements were not found.'
            );

            return;
        }

        if (form.dataset.chatBound === 'true') {
            return;
        }

        form.dataset.chatBound = 'true';

        const renderFiles = () => {
            this.renderSelectedFiles(
                preview,
                attachmentList,
                this.selectedFiles,
                index => {
                    this.selectedFiles.splice(
                        index,
                        1
                    );

                    renderFiles();
                }
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
                const result =
                    this.mergeSelectedFiles(
                        this.selectedFiles,
                        fileInput.files
                    );

                /*
                 * Clear the native input so the same
                 * file can be selected again.
                 */
                fileInput.value = '';

                if (result.error) {
                    this.showUploadStatus(
                        result.error,
                        true
                    );

                    return;
                }

                this.selectedFiles =
                    result.files;

                this.showUploadStatus('');
                renderFiles();
            }
        );

        form.addEventListener(
            'submit',
            async event => {
                event.preventDefault();

                const sent = await this.send(
                    input.value,
                    this.selectedFiles
                );

                if (sent) {
                    input.value = '';
                    this.clearSelectedFiles();
                }
            }
        );
    },

    mergeSelectedFiles(
        currentFiles,
        incomingFiles
    ) {
        const incoming =
            Array.from(incomingFiles ?? []);

        const oversizedFile =
            incoming.find(
                file =>
                    file.size >
                    this.maxAttachmentBytes
            );

        if (oversizedFile) {
            return {
                files: [...currentFiles],
                error:
                    `${oversizedFile.name} exceeds the 10 MB limit.`,
            };
        }

        const merged =
            [...currentFiles];

        incoming.forEach(file => {
            const duplicate =
                merged.some(existing => {
                    return (
                        existing.name === file.name &&
                        existing.size === file.size &&
                        existing.lastModified ===
                            file.lastModified
                    );
                });

            if (!duplicate) {
                merged.push(file);
            }
        });

        if (
            merged.length >
            this.maxAttachments
        ) {
            return {
                files: [...currentFiles],
                error:
                    'You can attach a maximum of 3 files.',
            };
        }

        return {
            files: merged,
            error: null,
        };
    },

    renderSelectedFiles(
        preview,
        list,
        files,
        onRemove
    ) {
        list.innerHTML = '';

        preview.hidden =
            files.length === 0;

        files.forEach((file, index) => {
            const item =
                document.createElement('div');

            item.className =
                'chat-selected-attachment';

            const icon =
                document.createElement('i');

            icon.className =
                file.type.startsWith('image/')
                    ? 'bi bi-image'
                    : 'bi bi-paperclip';

            const details =
                document.createElement('div');

            details.className =
                'chat-selected-attachment-details';

            const name =
                document.createElement('span');

            name.textContent =
                file.name;

            const size =
                document.createElement('small');

            size.textContent =
                this.formatFileSize(
                    file.size
                );

            details.appendChild(name);
            details.appendChild(size);

            const removeButton =
                document.createElement('button');

            removeButton.type = 'button';

            removeButton.className =
                'chat-selected-attachment-remove';

            removeButton.title =
                `Remove ${file.name}`;

            removeButton.setAttribute(
                'aria-label',
                `Remove ${file.name}`
            );

            const removeIcon =
                document.createElement('i');

            removeIcon.className =
                'bi bi-x-lg';

            removeButton.appendChild(
                removeIcon
            );

            removeButton.addEventListener(
                'click',
                () => {
                    onRemove(index);
                }
            );

            item.appendChild(icon);
            item.appendChild(details);
            item.appendChild(removeButton);

            list.appendChild(item);
        });
    },

    clearSelectedFiles() {
        this.selectedFiles = [];

        const fileInput =
            document.getElementById(
                'chatFile'
            );

        const preview =
            document.getElementById(
                'chatAttachmentPreview'
            );

        const attachmentList =
            document.getElementById(
                'chatAttachmentList'
            );

        if (fileInput) {
            fileInput.value = '';
        }

        if (attachmentList) {
            attachmentList.innerHTML = '';
        }

        if (preview) {
            preview.hidden = true;
        }
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

        const preview =
            document.getElementById(
                `${containerId}AttachmentPreview`
            );

        const attachmentList =
            document.getElementById(
                `${containerId}AttachmentList`
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
            !attachButton ||
            !preview ||
            !attachmentList
        ) {
            return;
        }

        let selectedFiles = [];

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

        const renderFiles = () => {
            this.renderSelectedFiles(
                preview,
                attachmentList,
                selectedFiles,
                index => {
                    selectedFiles.splice(
                        index,
                        1
                    );

                    renderFiles();
                }
            );
        };

        const clearAttachments = () => {
            selectedFiles = [];
            fileInput.value = '';
            attachmentList.innerHTML = '';
            preview.hidden = true;
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
                const result =
                    this.mergeSelectedFiles(
                        selectedFiles,
                        fileInput.files
                    );

                fileInput.value = '';

                if (result.error) {
                    showStatus(
                        result.error,
                        true
                    );

                    return;
                }

                selectedFiles =
                    result.files;

                showStatus('');
                renderFiles();
            }
        );

        form.addEventListener(
            'submit',
            async event => {
                event.preventDefault();

                const cleanMessage =
                    input.value.trim();

                if (
                    !cleanMessage &&
                    selectedFiles.length === 0
                ) {
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
                        selectedFiles
                    );

                    if (sent) {
                        input.value = '';
                        clearAttachments();
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
        this.destroyImageViewers();

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

                        <div id="${containerId}AttachmentList"
                             class="chat-attachment-list">
                        </div>
                    </div>

                    <small id="${containerId}UploadStatus"
                        class="chat-upload-status">
                    </small>
                    
                    <div class="chat-input-row">
                        <input type="file"
                            id="${containerId}File"
                            accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,.doc,.docx,.xls,.xlsx,.txt"
                            multiple
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

            this.clearSelectedFiles();
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

        this.destroyImageViewers();
        this.clearSelectedFiles();
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
    async send(
        message,
        files = []
    ) {
        const cleanMessage =
            String(message ?? '').trim();

        const normalizedFiles =
            Array.from(files ?? []);

        if (
            !cleanMessage &&
            normalizedFiles.length === 0
        ) {
            this.showUploadStatus(
                'Enter a message or attach a file.',
                true
            );

            return false;
        }

        if (
            normalizedFiles.length >
            this.maxAttachments
        ) {
            this.showUploadStatus(
                'You can attach a maximum of 3 files.',
                true
            );

            return false;
        }

        const oversizedFile =
            normalizedFiles.find(
                file =>
                    file.size >
                    this.maxAttachmentBytes
            );

        if (oversizedFile) {
            this.showUploadStatus(
                `${oversizedFile.name} exceeds the 10 MB limit.`,
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
                    normalizedFiles
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
                'Saved attachments:',
                savedMessage.attachments
            );

            this.appendMessage(savedMessage);

            this.socket?.emit(
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

            const validationErrors =
                error.response?.data?.errors;

            const validationMessage =
                validationErrors
                    ? Object.values(
                        validationErrors
                    ).flat()[0]
                    : null;

            this.showUploadStatus(
                validationMessage ??
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

    if (
        messageId &&
        messages.querySelector(
            `[data-message-id="${CSS.escape(messageId)}"]`
        )
    ) {
        return;
    }

    const attachments =
        this.normalizeAttachments(
            payload.attachments ??
            payload.attachment
        );

    const messageText =
        String(
            payload.message ?? ''
        ).trim();

    if (
        !messageText &&
        attachments.length === 0
    ) {
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

    meta.className =
        'chat-meta';

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

    let attachmentGallery = null;

    if (attachments.length > 0) {
        attachmentGallery =
            document.createElement('div');

        attachmentGallery.className =
            'chat-attachments-gallery';

        attachments.forEach(
            attachment => {
                this.appendAttachment(
                    attachmentGallery,
                    attachment
                );
            }
        );

        if (
            attachmentGallery
                .childElementCount > 0
        ) {
            bubble.appendChild(
                attachmentGallery
            );
        } else {
            attachmentGallery = null;
        }
    }

    row.appendChild(bubble);
    messages.appendChild(row);

    /*
     * Initialize one Viewer.js instance using the
     * complete attachment group.
     */
    if (attachmentGallery) {
        this.createImageGallery(
            attachmentGallery
        );
    }

    messages.scrollTop =
        messages.scrollHeight;
},

    appendAttachment(
    parent,
    rawAttachment
) {
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

        parent.appendChild(
            unavailable
        );

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
        const image =
            document.createElement('img');

        image.src =
            attachment.url;

        image.dataset.original =
            attachment.url;

        image.alt =
            attachment.name ??
            'Chat image';

        image.className =
            'chat-attachment-image';

        image.loading =
            'lazy';

        image.tabIndex = 0;

        image.setAttribute(
            'role',
            'button'
        );

        image.setAttribute(
            'aria-label',
            `View ${
                attachment.name ??
                'chat image'
            }`
        );

        image.addEventListener(
            'error',
            () => {
                console.error(
                    'Unable to load attachment image:',
                    attachment.url
                );

                image.hidden = true;

                const fallback =
                    document.createElement(
                        'span'
                    );

                fallback.className =
                    'chat-attachment-error';

                fallback.textContent =
                    attachment.name
                        ? `Unable to load ${attachment.name}`
                        : 'Unable to load image';

                container.appendChild(
                    fallback
                );
            }
        );

        container.appendChild(
            image
        );
    } else {
        const link =
            document.createElement('a');

        link.href =
            attachment.url;

        link.target =
            '_blank';

        link.rel =
            'noopener noreferrer';

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

    parent.appendChild(container);
},
createImageGallery(
    galleryElement
) {
    const images =
        Array.from(
            galleryElement.querySelectorAll(
                'img.chat-attachment-image'
            )
        );

    if (images.length === 0) {
        return;
    }

    const hasMultipleImages =
        images.length > 1;

    const viewerContainer =
        galleryElement.closest(
            [
                '#documentChatModal',
                '#messengerRoomsPanel',
                '#section-messenger',
            ].join(', ')
        ) ?? document.body;

    const viewer =
        new Viewer(
            galleryElement,
            {
                inline: false,

                container:
                    viewerContainer,

                /*
                 * Only chat attachment images should
                 * be included in this gallery.
                 */
                filter(image) {
                    return (
                        image.classList.contains(
                            'chat-attachment-image'
                        ) &&
                        !image.hidden
                    );
                },

                backdrop: true,
                button: true,
                keyboard: true,
                focus: true,
                fullscreen: true,

                movable: false,
                scalable: true,
                transition: true,

                /*
                 * Enables navigation between images.
                 */
                navbar:
                    hasMultipleImages,

                loop: true,

                zoomable: true,
                zoomOnTouch: true,
                zoomOnWheel: true,

                title: [
                    2,
                    image =>
                        image.alt ||
                        'Chat image',
                ],

                toolbar: {
                    prev:
                        hasMultipleImages,

                    zoomIn: true,
                    zoomOut: true,
                    reset: true,

                    next:
                        hasMultipleImages,

                    rotateLeft: true,
                    rotateRight: true,

                    flipHorizontal: true,
                    flipVertical: true,
                },

                url(image) {
                    return (
                        image.dataset.original ||
                        image.src
                    );
                },

                zIndex: 999999,
            }
        );

    this.imageViewers.push(
        viewer
    );

    /*
     * Viewer.js already handles normal mouse clicks.
     * This allows Enter and Space to open an image.
     */
    images.forEach(image => {
        image.addEventListener(
            'keydown',
            event => {
                if (
                    event.key !== 'Enter' &&
                    event.key !== ' '
                ) {
                    return;
                }

                event.preventDefault();

                /*
                 * The delegated click handler opens
                 * the correct image index.
                 */
                image.click();
            }
        );
    });
},
destroyImageViewers() {
    this.imageViewers.forEach(
        viewer => {
            try {
                viewer.destroy();
            } catch (error) {
                console.warn(
                    'Unable to destroy Viewer.js instance:',
                    error
                );
            }
        }
    );

    this.imageViewers = [];
},

    normalizeAttachments(value) {
    if (!value) {
        return [];
    }

    if (typeof value === 'string') {
        try {
            return this.normalizeAttachments(
                JSON.parse(value)
            );
        } catch (error) {
            console.error(
                'Invalid attachment JSON:',
                value,
                error
            );

            return [];
        }
    }

    if (Array.isArray(value)) {
        return value.filter(
            attachment => {
                return (
                    attachment &&
                    typeof attachment ===
                        'object' &&
                    (
                        attachment.path ||
                        attachment.url
                    )
                );
            }
        );
    }

    /*
     * Backward compatibility for messages
     * containing one attachment object.
     */
    if (
        typeof value === 'object' &&
        (
            value.path ||
            value.url
        )
    ) {
        return [value];
    }

    return [];
},

normalizeAttachment(value) {
    return (
        this.normalizeAttachments(
            value
        )[0] ?? null
    );
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