@props([
    'documents' => collect(),
])

<div class="documents-card">
    <div class="card-header">
        <h3>Document Chats</h3>
    </div>

    <div class="messenger-room-list">
        @forelse($documents as $doc)
            <button type="button"
                    class="messenger-room-item"
                    onclick="openDocumentChatFromMessenger(
                        {{ $doc->id }},
                        @js($doc->title)
                    )">
                <span class="room-title">{{ $doc->title }}</span>
                <small>{{ ucfirst($doc->status) }}</small>
            </button>
        @empty
            <div class="no-data">No document chats available.</div>
        @endforelse
    </div>
</div>