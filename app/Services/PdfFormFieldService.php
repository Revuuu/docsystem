<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use mikehaertl\pdftk\Pdf;
use RuntimeException;

class PdfFormFieldService
{
    /**
     * Read all AcroForm fields from a PDF.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getFields(string $pdfPath): array
    {
        if (!is_file($pdfPath)) {
            throw new RuntimeException(
                "PDF file does not exist: {$pdfPath}"
            );
        }

        $pdf = new Pdf($pdfPath, [
            'command' => config('pdftk.binary'),
            'useExec' => (bool) config(
                'pdftk.use_exec',
                false
            ),
        ]);

        $data = $pdf->getDataFields();

        if ($data === false) {
            throw new RuntimeException(
                'PDFtk could not read the PDF fields: ' .
                $pdf->getError()
            );
        }

        $fields = [];

        foreach ($data->__toArray() as $field) {
            $fieldName = trim(
                (string) ($field['FieldName'] ?? '')
            );

            if ($fieldName === '') {
                continue;
            }

            $fields[$fieldName] = $field;
        }

        return $fields;
    }

    /**
     * Confirm that required fields exist and are signature fields.
     */
    public function validateSignatureFields(
        string $pdfPath,
        array $requiredFieldNames
    ): void {
        $fields = $this->getFields($pdfPath);

        foreach ($requiredFieldNames as $fieldName) {
            if (!isset($fields[$fieldName])) {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "Required PDF signature field [{$fieldName}] is missing.",
                ]);
            }

            $fieldType = $fields[$fieldName]['FieldType']
                ?? null;

            if ($fieldType !== 'Signature') {
                throw ValidationException::withMessages([
                    'signature_template' =>
                        "PDF field [{$fieldName}] must be a signature field.",
                ]);
            }
        }
    }
}