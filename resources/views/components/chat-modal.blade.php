<div class="modal-overlay chat-modal-overlay"
     id="documentChatModal"
     style="display: none;">

    <div class="chat-modal">

        <div class="chat-header">
            <div>
                <h3 id="chatDocumentTitle">
                    Document Chat
                </h3>

                <small>
                    Document conversation room
                </small>
            </div>

            <button type="button"
                    class="chat-close-btn"
                    onclick="closeDocumentChat()"
                    aria-label="Close document chat">

                <i class="bi bi-x-circle-fill text-danger"></i>
            </button>
        </div>

        <input type="hidden"
               id="activeDocumentId">

        <div id="chatMessages"
             class="chat-messages">
        </div>

        <form id="chatForm"
              class="chat-form"
              enctype="multipart/form-data">

            <div id="chatAttachmentPreview"
                 class="chat-attachment-preview"
                 hidden>

                <img id="chatAttachmentImagePreview"
                     class="chat-attachment-image-preview"
                     alt="Selected attachment preview"
                     hidden>

                <div class="chat-attachment-details">
                    <span id="chatAttachmentName"></span>
                    <small id="chatAttachmentSize"></small>
                </div>

                <button type="button"
                        id="removeChatAttachment"
                        class="remove-chat-attachment"
                        title="Remove attachment"
                        aria-label="Remove attachment">

                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="chat-input-row">

                <input type="file"
                       id="chatFile"
                       name="file"
                       accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,.doc,.docx,.xls,.xlsx,.txt"
                       hidden>

                <button type="button"
                        id="chatAttachButton"
                        class="chat-attach-btn"
                        title="Attach a file"
                        aria-label="Attach a file">

                    <i class="bi bi-paperclip"></i>
                </button>

                <input type="text"
                       id="chatInput"
                       name="message"
                       placeholder="Type a message..."
                       autocomplete="off">

                <button type="submit"
                        id="chatSendButton"
                        class="chat-send-btn">

                    Send
                </button>
            </div>

            <small id="chatUploadStatus"
                   class="chat-upload-status">
            </small>

        </form>

    </div>

</div>