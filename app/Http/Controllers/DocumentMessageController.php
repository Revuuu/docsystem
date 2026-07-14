<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class DocumentMessageController extends Controller
{
    private const MAX_ATTACHMENTS = 3;

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
        $request->validate(
            [
                'message' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'attachments' => [
                    'nullable',
                    'array',
                    'max:' . self::MAX_ATTACHMENTS,
                ],

                'attachments.*' => [
                    'required',
                    'file',
                    'max:10240',
                    'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt',
                ],

                /*
                 * Temporary backward compatibility
                 * with the previous single-file frontend.
                 */
                'attachment' => [
                    'nullable',
                    'file',
                    'max:10240',
                    'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt',
                ],
            ],
            [
                'attachments.max' =>
                    'You can attach a maximum of 3 files.',

                'attachments.*.max' =>
                    'Each attachment must not exceed 10 MB.',

                'attachments.*.mimes' =>
                    'One or more attachments have an unsupported file type.',

                'attachment.max' =>
                    'Each attachment must not exceed 10 MB.',

                'attachment.mimes' =>
                    'The attachment has an unsupported file type.',
            ]
        );

        $messageText = trim(
            (string) $request->input(
                'message',
                ''
            )
        );

        $files = collect(
            $request->file(
                'attachments',
                []
            )
        )
            ->filter(
                fn ($file) =>
                    $file instanceof UploadedFile
            )
            ->values();

        if ($request->hasFile('attachment')) {
            $legacyAttachment =
                $request->file('attachment');

            if (
                $legacyAttachment instanceof
                UploadedFile
            ) {
                $files->push(
                    $legacyAttachment
                );
            }
        }

        if (
            $files->count() >
            self::MAX_ATTACHMENTS
        ) {
            throw ValidationException::withMessages([
                'attachments' => [
                    'You can attach a maximum of 3 files.',
                ],
            ]);
        }

        if (
            $messageText === '' &&
            $files->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'message' => [
                    'Enter a message or attach a file.',
                ],
            ]);
        }

        $attachments = [];

        try {
            foreach ($files as $file) {
                $attachments[] =
                    $this->storeAttachment(
                        $file,
                        $document
                    );
            }

            $message = DocumentMessage::create([
                'document_id' =>
                    $document->id,

                'user_id' =>
                    $request->user()->id,

                'message' =>
                    $messageText !== ''
                        ? $messageText
                        : null,

                'attachment' =>
                    $attachments !== []
                        ? $attachments
                        : null,
            ]);
        } catch (Throwable $exception) {
            foreach ($attachments as $attachment) {
                Storage::disk(
                    $attachment['disk'] ??
                    'local'
                )->delete(
                    $attachment['path']
                );
            }

            throw $exception;
        }

        $message->load('user');

        return response()->json(
            $this->formatMessage($message),
            201
        );
    }

    public function attachment(
        DocumentMessage $documentMessage,
        int $attachmentIndex = 0
    ) {
        $attachments =
            $this->normalizeAttachments(
                $documentMessage->attachment
            );

        $attachment =
            $attachments[$attachmentIndex] ??
            null;

        abort_if(
            !is_array($attachment) ||
            empty($attachment['path']),
            404
        );

        $disk =
            $attachment['disk'] ??
            'local';

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

    private function storeAttachment(
        UploadedFile $file,
        Document $document
    ): array {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $storedName =
            Str::uuid()->toString();

        if ($extension !== '') {
            $storedName .= ".{$extension}";
        }

        $storedPath = $file->storeAs(
            "chat-attachments/{$document->id}",
            $storedName,
            'local'
        );

        if (!is_string($storedPath)) {
            throw new RuntimeException(
                'The attachment could not be stored.'
            );
        }

        $mimeType =
            $file->getMimeType() ??
            'application/octet-stream';

        return [
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

    private function formatMessage(
        DocumentMessage $message
    ): array {
        $attachments = collect(
            $this->normalizeAttachments(
                $message->attachment
            )
        )
            ->map(
                function (
                    array $attachment,
                    int $index
                ) use ($message): array {
                    $disk =
                        $attachment['disk'] ??
                        'local';

                    $path =
                        $attachment['path'];

                    $exists =
                        Storage::disk($disk)
                            ->exists($path);

                    $attachment['url'] =
                        $exists
                            ? route(
                                'document-messages.attachments.show',
                                [
                                    'documentMessage' =>
                                        $message,

                                    'attachmentIndex' =>
                                        $index,
                                ],
                                false
                            )
                            : null;

                    $attachment['missing'] =
                        !$exists;

                    return $attachment;
                }
            )
            ->values()
            ->all();

        return [
            'id' => $message->id,

            'document_id' =>
                $message->document_id,

            'user_id' =>
                $message->user_id,

            'user_name' =>
                $message->user?->name ??
                'Unknown user',

            'message' =>
                $message->message,

            /*
             * New frontend field.
             */
            'attachments' =>
                $attachments,

            /*
             * Backward compatibility for an older
             * cached version of chat.js.
             */
            'attachment' =>
                $attachments[0] ?? null,

            'sent_at' =>
                $message->created_at
                    ?->format('h:i A'),
        ];
    }

    private function normalizeAttachments(
        mixed $attachment
    ): array {
        if (
            !is_array($attachment) ||
            $attachment === []
        ) {
            return [];
        }

        /*
         * Existing single-attachment record.
         */
        if (!empty($attachment['path'])) {
            return [$attachment];
        }

        /*
         * New multi-attachment record.
         */
        return array_values(
            array_filter(
                $attachment,
                fn ($item) =>
                    is_array($item) &&
                    !empty($item['path'])
            )
        );
    }
}
