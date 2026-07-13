<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentMessageController extends Controller
{
    public function index(
        Document $document
    ): JsonResponse {
        $messages = DocumentMessage::query()
            ->with('user')
            ->where('document_id', $document->id)
            ->oldest()
            ->get()
            ->map(
                fn (DocumentMessage $message) =>
                    $this->formatMessage($message)
            )
            ->values();

        return response()->json($messages);
    }

    public function store(
        Request $request,
        Document $document
    ): JsonResponse {
        $request->validate([
            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt',
            ],
        ]);

        $messageText = trim(
            (string) $request->input('message', '')
        );

        if (
            $messageText === '' &&
            !$request->hasFile('attachment')
        ) {
            throw ValidationException::withMessages([
                'message' => [
                    'Enter a message or attach a file.',
                ],
            ]);
        }

        $attachmentData = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $storedName = Str::uuid()->toString();

            if ($extension !== '') {
                $storedName .= ".{$extension}";
            }

            $storedPath = $file->storeAs(
                "chat-attachments/{$document->id}",
                $storedName,
                'local'
            );

            $mimeType =
                $file->getMimeType() ??
                'application/octet-stream';

            $attachmentData = [
                'disk' => 'local',
                'path' => $storedPath,
                'name' => basename(
                    $file->getClientOriginalName()
                ),
                'mime_type' => $mimeType,
                'size' => $file->getSize(),
                'is_image' => str_starts_with(
                    $mimeType,
                    'image/'
                ),
            ];
        }

        $message = DocumentMessage::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,

            'message' => $messageText !== ''
                ? $messageText
                : null,

            'attachment' => $attachmentData,
        ]);

        $message->load('user');

        return response()->json(
            $this->formatMessage($message),
            201
        );
    }

    public function attachment(
        DocumentMessage $documentMessage
    ) {
        $attachment =
            $documentMessage->attachment;

        abort_if(
            !is_array($attachment) ||
            empty($attachment['path']),
            404
        );

        $disk =
            $attachment['disk'] ?? 'local';

        $path =
            $attachment['path'];

        abort_unless(
            Storage::disk($disk)->exists($path),
            404
        );

        return response()->file(
            Storage::disk($disk)->path($path),
            [
                'Content-Type' =>
                    $attachment['mime_type'] ??
                    'application/octet-stream',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }

    private function formatMessage(
        DocumentMessage $message
    ): array {
        $attachment =
            $message->attachment;

        if (
            is_array($attachment) &&
            !empty($attachment['path'])
        ) {
            $attachment['url'] = route(
                'document-messages.attachment',
                [
                    'documentMessage' => $message,
                ],
                false
            );
        }

        return [
            'id' => $message->id,
            'document_id' => $message->document_id,
            'user_id' => $message->user_id,

            'user_name' =>
                $message->user?->name ??
                'Unknown user',

            'message' => $message->message,
            'attachment' => $attachment,

            'sent_at' =>
                $message->created_at
                    ?->format('h:i A'),
        ];
    }
}