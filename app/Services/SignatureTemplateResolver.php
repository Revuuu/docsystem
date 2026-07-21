<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Http\UploadedFile;

class SignatureTemplateResolver
{
    public function __construct(
        private readonly PdfFormFieldService $pdfFormFieldService
    ) {
    }

    /**
     * Resolve an uploaded PDF using active database templates
     * and their configured PDF signature fields.
     */
    public function resolve(UploadedFile $uploadedFile): ?array
    {
        if (!$uploadedFile->isValid()) {
            return null;
        }

        $realPath = $uploadedFile->getRealPath();

        if (!$realPath || !is_file($realPath)) {
            return null;
        }

        /*
         * Read the uploaded PDF fields once.
         */
        try {
            $uploadedFields = $this->pdfFormFieldService
                ->getFields($realPath);
        } catch (\Throwable) {
            /*
             * PDFs without readable form fields are treated
             * as ordinary documents.
             */
            return null;
        }

        /*
         * Only database templates marked active may classify
         * newly uploaded documents.
         */
        $activeTemplates = DocumentTemplate::query()
            ->where('is_active', true)
            ->orderBy('template_key')
            ->get();

        foreach ($activeTemplates as $databaseTemplate) {
            $templateKey = $databaseTemplate->template_key;

            $definition = config(
                "signature_templates.{$templateKey}"
            );

            if (!is_array($definition)) {
                continue;
            }

            $requiredFields = collect(
                $definition['blocks'] ?? []
            )
                ->pluck('field_name')
                ->filter(
                    fn ($fieldName): bool =>
                        is_string($fieldName) &&
                        trim($fieldName) !== ''
                )
                ->map(
                    fn (string $fieldName): string =>
                        trim($fieldName)
                )
                ->unique()
                ->values();

            if ($requiredFields->isEmpty()) {
                continue;
            }

            $matchesTemplate = $requiredFields->every(
                function (string $fieldName) use (
                    $uploadedFields
                ): bool {
                    if (!isset($uploadedFields[$fieldName])) {
                        return false;
                    }

                    return (
                        $uploadedFields[$fieldName]['FieldType']
                        ?? null
                    ) === 'Signature';
                }
            );

            if (!$matchesTemplate) {
                continue;
            }

            return array_merge(
                $definition,
                [
                    'key' => $templateKey,

                    /*
                     * Metadata from the active database record.
                     */
                    'document_template_id' =>
                        $databaseTemplate->id,

                    'template_version' =>
                        $databaseTemplate->version,

                    'template_file_path' =>
                        $databaseTemplate->file_path,
                ]
            );
        }

        /*
         * No active template structure matched.
         */
        return null;
    }
}