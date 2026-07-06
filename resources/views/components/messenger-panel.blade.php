@props([
    'documents' => collect(),
])

<div class="documents-card messenger-card">

    <div class="messenger-layout">

        <div class="messenger-sidebar">

            <div class="messenger-search-box">
                <i class="bi bi-search"></i>

                <input
                    type="text"
                    id="chatSearchInput"
                    placeholder="Search messages...">
            </div>

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

                            <div class="room-title-row">
                                <strong class="room-title">
                                    {{ $doc->title }}
                                </strong>

                                <span class="room-status status-{{ strtolower($doc->status) }}">
                                    {{ ucfirst($doc->status) }}
                                </span>

                                @forelse($doc->approvals as $approval)
                                    <small>
                                        {{ $approval->user?->name ?? 'Unknown user' }}
                                    </small>
                                @empty
                                    <small>No user assigned</small>
                                @endforelse

                                <small>
                                    {{ $doc->created_at?->format('M j, Y') }}
                                </small>
                            </div>

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

            <div id="panelConversationContainer" class="conversation-panel">
                <div class="empty-chat">
                    Select a document to begin chatting.
                </div>
            </div>

        </div>

    </div>

</div>