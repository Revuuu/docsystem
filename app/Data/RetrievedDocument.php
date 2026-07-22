<?php

namespace App\Data;

/**
 * Represents a PDF retrieved from a temporary
 * or external document source.
 */
final readonly class RetrievedDocument
{
    public function __construct(
        public string $templateKey,
        public string $title,
        public string $sourcePath,
        public string $referenceTemplatePath,
        public string $originalFileName,
        public string $mimeType,
        public int $fileSize,
    ) {
    }
}