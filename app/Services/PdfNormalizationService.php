<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class PdfNormalizationService
{
    /**
     * Convert a PDF into a format readable by the free FPDI parser.
     *
     * The returned file is temporary and should be deleted after use.
     */
    public function normalizeForFpdi(
        string $inputPath
    ): string {
        if (!is_file($inputPath)) {
            throw new RuntimeException(
                "PDF file does not exist: {$inputPath}"
            );
        }

        $temporaryDirectory = storage_path(
            'app/private/tmp/fpdi'
        );

        if (
            !is_dir($temporaryDirectory) &&
            !mkdir(
                $temporaryDirectory,
                0755,
                true
            ) &&
            !is_dir($temporaryDirectory)
        ) {
            throw new RuntimeException(
                'Unable to create the temporary FPDI directory.'
            );
        }

        $outputPath =
            $temporaryDirectory .
            DIRECTORY_SEPARATOR .
            'fpdi_' .
            bin2hex(random_bytes(16)) .
            '.pdf';

        $process = new Process([
            $this->resolveQpdfBinary(),

            '--warning-exit-0',

            '--object-streams=disable',
            '--force-version=1.4',

            $inputPath,
            $outputPath,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (
            !$process->isSuccessful() ||
            !is_file($outputPath)
        ) {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }

            throw new RuntimeException(
                'QPDF could not normalize the PDF: ' .
                trim(
                    $process->getErrorOutput() ?:
                    $process->getOutput()
                )
            );
        }

        return $outputPath;
    }

    /**
     * Find QPDF on Windows, Linux, or macOS.
     */
    /**
 * Find QPDF using the configured path first,
 * followed by common operating-system locations.
 */
private function resolveQpdfBinary(): string
{
    $configuredBinary = config(
        'qpdf.binary',
        'qpdf'
    );

    $candidates = [];

    if (
        is_string($configuredBinary)
        && trim($configuredBinary) !== ''
    ) {
        $candidates[] = trim(
            $configuredBinary
        );
    }

    $candidates = array_merge(
        $candidates,
        [
            'qpdf',

            '/opt/homebrew/bin/qpdf',
            '/usr/local/bin/qpdf',
            '/usr/bin/qpdf',
        ]
    );

    if (PHP_OS_FAMILY === 'Windows') {
        $windowsPaths = array_merge(
            glob(
                'C:/Program Files/qpdf*/bin/qpdf.exe'
            ) ?: [],

            glob(
                'C:/Program Files (x86)/qpdf*/bin/qpdf.exe'
            ) ?: []
        );

        $candidates = array_merge(
            $candidates,
            $windowsPaths
        );
    }

    foreach (
        array_unique($candidates)
        as $candidate
    ) {
        try {
            $process = new Process([
                $candidate,
                '--version',
            ]);

            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        } catch (Throwable) {
            continue;
        }
    }

    throw new RuntimeException(
        'QPDF executable could not be found. ' .
        'Check QPDF_BINARY in .env or add QPDF to the system PATH.'
    );
}
}