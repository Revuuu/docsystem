<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentMessage;

class DocumentMessageController extends Controller
{
    public function index(Document $document)
    {
        return DocumentMessage::with('user')
            ->where('document_id', $document->id)
            ->oldest()
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'document_id' => $message->document_id,
                'user_id' => $message->user_id,
                'user_name' => $message->user->name,
                'message' => $message->message,
                'sent_at' => $message->created_at->format('h:i A'),
            ]);
    }

    public function store(Request $request, Document $document)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = DocumentMessage::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        $message->load('user');

        return [
            'id' => $message->id,
            'document_id' => $message->document_id,
            'user_id' => $message->user_id,
            'user_name' => $message->user->name,
            'message' => $message->message,
            'sent_at' => $message->created_at->format('h:i A'),
        ];
    }
}
