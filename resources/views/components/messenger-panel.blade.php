@props([
    'documents' => collect(),
])

<div class="documents-card">

    <div class="card-header">
        <h3>Document Chats</h3>
    </div>

    <div class="messenger-layout">

        <div class="messenger-sidebar">
            <div class="messenger-room-list">
                @forelse($documents as $doc)

                    <button type="button"
                            class="messenger-room-item"
                            onclick="openMessengerRoom(
                                {{ $doc->id }},
                                @js($doc->title),
                                'panelConversationContainer'
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

            <div id="panelConversationContainer">
                <div class="empty-chat">
                    Select a document to begin chatting.
                </div>
            </div>

        </div>

    </div>

</div>