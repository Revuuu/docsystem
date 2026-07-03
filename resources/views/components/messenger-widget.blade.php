@props([
    'documents' => collect(),
])

<div id="messengerWidget"
     class="messenger-widget">

    <button type="button"
            class="messenger-float-btn"
            onclick="toggleMessengerRooms()">

        <i class="bi bi-chat-dots-fill"></i>

        <span id="messengerUnreadBadge"
              class="messenger-unread-badge">
            0
        </span>

    </button>

    <div id="messengerRoomsPanel"
         class="messenger-rooms-panel">

        <div class="messenger-rooms-panel-inner">

            <div class="messenger-header">
                <strong>Document Chats</strong>

                <button type="button"
                        class="chat-close-btn"
                        onclick="toggleMessengerRooms()">
                    <i class="bi bi-x-circle-fill text-danger"></i>
                </button>
            </div>

            <div class="messenger-layout">

                <div class="messenger-sidebar">
                    <div class="messenger-title">
                        <strong>Document Title</strong>
                    </div>
                    <div class="messenger-room-list">
                        @forelse($documents as $doc)

                            <button type="button"
                                    class="messenger-room-item"
                                    onclick="openMessengerRoom(
                                        {{ $doc->id }},
                                        @js($doc->title),
                                        'widgetConversationContainer' 
                                    )">

                                <div class="room-info">
                                    <strong>
                                        {{ $doc->title }}
                                    </strong>

                                    <small>
                                        {{ ucfirst($doc->status) }}
                                    </small>
                                </div>

                            </button>

                        @empty

                            <div class="no-data">
                                No document chats available.
                            </div>

                        @endforelse
                    </div>

                </div>

                <div class="messenger-content">

                    <div id="widgetConversationContainer">
                        <div class="empty-chat">
                            Select a document to begin chatting.
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    window.messengerDocumentIds = @json($documents->pluck('id')->values());
</script>