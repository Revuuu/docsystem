<?php

namespace App\Services;

use App\Data\RetrievedDocument;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Tcpdf\Fpdi;
use RuntimeException;
use Throwable;

class PdfTemplateCompatibilityService
{
    /**
     * Maximum allowed page-dimension difference in millimeters.
     */
    private const PAGE_SIZE_TOLERANCE = 0.5;

    public function __construct(
        private readonly PdfNormalizationService $pdfNormalizationService,
        private readonly PdfFormFieldService $pdfFormFieldService,
    ) {
    }

    /**
     * Validate an incoming PDF against its configured reference template.
     *
     * @return array<string, mixed>
     */
    public function assertCompatible(
        RetrievedDocument $document
    ): array {
        $template = config(
            "signature_templates.{$document->templateKey}"
        );

        if (!is_array($template)) {
            throw ValidationException::withMessages([
                'document_type' =>
                    "No signature template is configured for [{$document->templateKey}].",
            ]);
        }

        $blocks = collect(
            $template['blocks'] ?? []
        );

        if ($blocks->isEmpty()) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    "Template [{$document->templateKey}] has no signature blocks.",
            ]);
        }

        $requiredFieldNames = $blocks
            ->pluck('field_name')
            ->filter(
                fn (mixed $fieldName): bool =>
                    is_string($fieldName)
                    && trim($fieldName) !== ''
            )
            ->map(
                fn (string $fieldName): string =>
                    trim($fieldName)
            )
            ->unique()
            ->values()
            ->all();

        if ($requiredFieldNames === []) {
            throw ValidationException::withMessages([
                'signature_template' =>
                    "Template [{$document->templateKey}] has no PDF signature field names.",
            ]);
        }

        /*
         * The reference overlay must contain the Sejda fields
         * defined in config/signature_templates.php.
         */
        $this->pdfFormFieldService
            ->validateSignatureFields(
                $document->referenceTemplatePath,
                $requiredFieldNames
            );

        $sourceNormalizedPath = null;
        $referenceNormalizedPath = null;

        try {
            $sourceNormalizedPath =
                $this->pdfNormalizationService
                    ->normalizeForFpdi(
                        $document->sourcePath
                    );

            $referenceNormalizedPath =
                $this->pdfNormalizationService
                    ->normalizeForFpdi(
                        $document->referenceTemplatePath
                    );

            $sourcePages = $this->readPageGeometry(
                $sourceNormalizedPath
            );

            $referencePages = $this->readPageGeometry(
                $referenceNormalizedPath
            );

            $this->assertPageGeometryMatches(
                $sourcePages,
                $referencePages
            );

            $this->assertBlocksFitDocument(
                $blocks->all(),
                count($sourcePages)
            );
        } finally {
            $this->deleteTemporaryFile(
                $sourceNormalizedPath
            );

            $this->deleteTemporaryFile(
                $referenceNormalizedPath
            );
        }

        /*
         * Guarantee the key exists for
         * DocumentSignatureBlockService.
         */
        $template['key'] =
            $template['key']
            ?? $document->templateKey;

        return $template;
    }

    /**
     * Read the dimensions of every page in a normalized PDF.
     *
     * @return array<int, array{
     *     page_number: int,
     *     width: float,
     *     height: float
     * }>
     */
    private function readPageGeometry(
        string $pdfPath
    ): array {
        try {
            $pdf = new Fpdi();

            $pdf->SetAutoPageBreak(
                false
            );

            $pageCount = $pdf->setSourceFile(
                $pdfPath
            );

            if ($pageCount < 1) {
                throw new RuntimeException(
                    "PDF contains no pages: {$pdfPath}"
                );
            }

            $pages = [];

            for (
                $pageNumber = 1;
                $pageNumber <= $pageCount;
                $pageNumber++
            ) {
                $templateId = $pdf->importPage(
                    $pageNumber
                );

                $size = $pdf->getTemplateSize(
                    $templateId
                );

                if (
                    !is_array($size)
                    || !isset(
                        $size['width'],
                        $size['height']
                    )
                ) {
                    throw new RuntimeException(
                        "Unable to read page {$pageNumber} dimensions."
                    );
                }

                $pages[] = [
                    'page_number' =>
                        $pageNumber,

                    'width' =>
                        (float) $size['width'],

                    'height' =>
                        (float) $size['height'],
                ];
            }

            return $pages;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Unable to inspect PDF page geometry: '
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    /**
     * Compare the incoming PDF with the reference template.
     */
    private function assertPageGeometryMatches(
        array $sourcePages,
        array $referencePages
    ): void {
        if (
            count($sourcePages)
            !== count($referencePages)
        ) {
            throw ValidationException::withMessages([
                'source_pdf' =>
                    'The incoming PDF page count does not match the reference template. '
                    . 'Incoming: '
                    . count($sourcePages)
                    . ', reference: '
                    . count($referencePages)
                    . '.',
            ]);
        }

        foreach (
            $sourcePages as $index => $sourcePage
        ) {
            $referencePage =
                $referencePages[$index];

            $widthDifference = abs(
                $sourcePage['width']
                - $referencePage['width']
            );

            $heightDifference = abs(
                $sourcePage['height']
                - $referencePage['height']
            );

            if (
                $widthDifference
                    > self::PAGE_SIZE_TOLERANCE
                || $heightDifference
                    > self::PAGE_SIZE_TOLERANCE
            ) {
                $pageNumber =
                    $sourcePage['page_number'];

                throw ValidationException::withMessages([
                    'source_pdf' =>
                        "Incoming PDF page {$pageNumber} does not match the reference template dimensions. "
                        . 'Incoming: '
                        . round(
                            $sourcePage['width'],
                            2
                        )
                        . ' × '
                        . round(
                            $sourcePage['height'],
                            2
                        )
                        . ' mm; reference: '
                        . round(
                            $referencePage['width'],
                            2
                        )
                        . ' × '
                        . round(
                            $referencePage['height'],
                            2
                        )
                        . ' mm.',
                ]);
            }
        }
    }

    /**
     * Ensure each configured block points to an existing page
     * and remains within normalized page boundaries.
     */
    private function assertBlocksFitDocument(
        array $blocks,
        int $pageCount
    ): void {
        foreach ($blocks as $index => $block) {
            if (!is_array($block)) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        'A signature block definition is invalid.',
                ]);
            }

            $label =
                $block['label']
                ?? 'Block ' . ($index + 1);

            $pageNumber = (int) (
                $block['page_number']
                ?? 0
            );

            if (
                $pageNumber < 1
                || $pageNumber > $pageCount
            ) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Signature block [{$label}] points to missing page {$pageNumber}.",
                ]);
            }

            foreach (
                ['x', 'y', 'width', 'height']
                as $coordinate
            ) {
                if (
                    !array_key_exists(
                        $coordinate,
                        $block
                    )
                    || !is_numeric(
                        $block[$coordinate]
                    )
                ) {
                    throw ValidationException::withMessages([
                        'signature_template' =>
                            "Signature block [{$label}] has an invalid [{$coordinate}] value.",
                    ]);
                }
            }

            $x = (float) $block['x'];
            $y = (float) $block['y'];
            $width = (float) $block['width'];
            $height = (float) $block['height'];

            if (
                $x < 0
                || $y < 0
                || $width <= 0
                || $height <= 0
                || $x > 1
                || $y > 1
                || $width > 1
                || $height > 1
                || ($x + $width) > 1
                || ($y + $height) > 1
            ) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Signature block [{$label}] falls outside the PDF page.",
                ]);
            }
        }
    }

    /**
     * Remove a temporary normalized PDF.
     */
    private function deleteTemporaryFile(
        ?string $path
    ): void {
        if (
            is_string($path)
            && is_file($path)
        ) {
            @unlink($path);
        }
    }
}