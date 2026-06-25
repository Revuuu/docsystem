<div class="modal-overlay chat-modal-overlay"
    id="documentChatModal"
    style="display:none;">

    <div class="chat-modal">

        <div class="chat-header">
            <div>
                <h3 id="chatDocumentTitle">Document Chat</h3>
                <small>Document conversation room</small>
            </div>

            <button type="button"
                    class="chat-close-btn"
                    onclick="closeDocumentChat()">
                <i class="bi bi-x-circle-fill text-danger"></i>
            </button>
        </div>

        <input type="hidden" id="activeDocumentId">

        <div id="chatMessages" class="chat-messages"></div>

        <form id="chatForm" class="chat-form">
            <input type="text"
                id="chatInput"
                placeholder="Type a message..."
                autocomplete="off">

            <button type="submit">
                Send
            </button>
        </form>
    </div>
</div>