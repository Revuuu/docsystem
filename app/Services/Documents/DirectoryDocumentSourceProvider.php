<?php

namespace App\Services\Documents;

use App\Contracts\DocumentSourceProvider;
use App\Data\RetrievedDocument;
use RuntimeException;

class DirectoryDocumentSourceProvider implements DocumentSourceProvider
{
    public function retrieve(
        string $documentType
    ): RetrievedDocument {
        $configuration = config(
            "document_extraction.document_types.{$documentType}"
        );

        if (!is_array($configuration)) {
            throw new RuntimeException(
                "No extraction configuration exists for document type [{$documentType}]."
            );
        }

        $templateKey = $this->requiredString(
            $configuration,
            'template_key',
            $documentType
        );

        $title = $this->requiredString(
            $configuration,
            'title',
            $documentType
        );

        $sourcePath = $this->requiredString(
            $configuration,
            'source_path',
            $documentType
        );

        $referenceTemplatePath = $this->requiredString(
            $configuration,
            'reference_template_path',
            $documentType
        );

        $resolvedSourcePath = $this->validatePdf(
            $sourcePath,
            'source PDF'
        );

        $resolvedReferencePath = $this->validatePdf(
            $referenceTemplatePath,
            'reference template PDF'
        );

        $fileSize = filesize(
            $resolvedSourcePath
        );

        if ($fileSize === false || $fileSize < 1) {
            throw new RuntimeException(
                "The source PDF [{$resolvedSourcePath}] is empty or unreadable."
            );
        }

        return new RetrievedDocument(
            templateKey: $templateKey,
            title: $title,
            sourcePath: $resolvedSourcePath,
            referenceTemplatePath: $resolvedReferencePath,
            originalFileName: basename($resolvedSourcePath),
            mimeType: 'application/pdf',
            fileSize: $fileSize,
        );
    }

    /**
     * Read a required string from a document-type configuration.
     */
    private function requiredString(
        array $configuration,
        string $key,
        string $documentType
    ): string {
        $value = $configuration[$key] ?? null;

        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException(
                "Missing extraction configuration [{$key}] for document type [{$documentType}]."
            );
        }

        return trim($value);
    }

    /**
     * Confirm that the path points to a readable PDF file.
     */
    private function validatePdf(
        string $path,
        string $description
    ): string {
        if (!is_file($path)) {
            throw new RuntimeException(
                "The configured {$description} does not exist: {$path}"
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                "The configured {$description} is not readable: {$path}"
            );
        }

        $resolvedPath = realpath($path);

        if ($resolvedPath === false) {
            throw new RuntimeException(
                "The configured {$description} path could not be resolved: {$path}"
            );
        }

        if (strtolower(
            pathinfo($resolvedPath, PATHINFO_EXTENSION)
        ) !== 'pdf') {
            throw new RuntimeException(
                "The configured {$description} is not a PDF file: {$resolvedPath}"
            );
        }

        $handle = fopen(
            $resolvedPath,
            'rb'
        );

        if ($handle === false) {
            throw new RuntimeException(
                "The configured {$description} could not be opened: {$resolvedPath}"
            );
        }

        try {
            $header = fread(
                $handle,
                5
            );
        } finally {
            fclose($handle);
        }

        if ($header !== '%PDF-') {
            throw new RuntimeException(
                "The configured {$description} does not contain a valid PDF header: {$resolvedPath}"
            );
        }

        return $resolvedPath;
    }
}