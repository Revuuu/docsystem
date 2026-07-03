@props([
    'documents' => collect(),
])

<div class="messenger-layout">

    <div class="messenger-sidebar">
           <div class="messenger-title">
                <strong>Document Title</strong>
            </div>
        <div class="messenger-room-list">

            @foreach($documents as $doc)

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

            @endforeach

        </div>

    </div>

    <div class="messenger-content">

        <div
            id="panelConversationContainer">

            <div class="empty-chat">

                Select a document to begin chatting.

            </div>

        </div>

    </div>

</div>