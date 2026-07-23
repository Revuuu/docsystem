<?php

namespace App\Services\Pdf;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ChromePdfRenderer
{
    /**
     * Render complete HTML into a PDF and return the temporary PDF path.
     */
    public function render(
        string $html,
        string $suggestedFilename = 'document.pdf'
    ): string {
        if (trim($html) === '') {
            throw ValidationException::withMessages([
                'pdf_generation' =>
                    'The Purchase Order template rendered empty HTML.',
            ]);
        }

        $binary = $this->resolveBinary();
        $temporaryDirectory = $this->temporaryDirectory();
        $token = Str::uuid()->toString();
        $htmlPath = $temporaryDirectory . DIRECTORY_SEPARATOR
            . $token . '.html';
        $safeFilename = pathinfo(
            basename($suggestedFilename),
            PATHINFO_FILENAME
        ) ?: 'document';
        $pdfPath = $temporaryDirectory . DIRECTORY_SEPARATOR
            . $token . '-' . $safeFilename . '.pdf';
        $profileDirectory = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . $token . '-chrome-profile';

        if (!mkdir($profileDirectory, 0775, true) && !is_dir($profileDirectory)) {
            throw ValidationException::withMessages([
                'pdf_generation' =>
                    'The temporary Chrome profile directory could not be created.',
            ]);
        }

        try {
            if (file_put_contents($htmlPath, $html) === false) {
                throw ValidationException::withMessages([
                    'pdf_generation' =>
                        'The temporary Purchase Order HTML could not be written.',
                ]);
            }

            $process = new Process([
                $binary,
                '--headless=new',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-background-networking',
                '--disable-extensions',
                '--disable-sync',
                '--metrics-recording-only',
                '--mute-audio',
                '--no-first-run',
                '--allow-file-access-from-files',
                '--user-data-dir=' . $profileDirectory,
                '--no-pdf-header-footer',
                '--run-all-compositor-stages-before-draw',
                '--virtual-time-budget=2000',
                '--print-to-pdf=' . $pdfPath,
                $this->fileUri($htmlPath),
            ]);
            $process->setTimeout(
                max(
                    10,
                    (int) config(
                        'pdf_renderer.timeout_seconds',
                        90
                    )
                )
            );
            $process->run();

            if (!$process->isSuccessful()) {
                throw ValidationException::withMessages([
                    'pdf_generation' => sprintf(
                        'Chrome could not generate the Purchase Order PDF: %s',
                        trim(
                            $process->getErrorOutput()
                            ?: $process->getOutput()
                            ?: 'unknown rendering error'
                        )
                    ),
                ]);
            }

            if (
                !is_file($pdfPath) ||
                filesize($pdfPath) < 5 ||
                file_get_contents(
                    $pdfPath,
                    false,
                    null,
                    0,
                    5
                ) !== '%PDF-'
            ) {
                throw ValidationException::withMessages([
                    'pdf_generation' =>
                        'Chrome did not create a valid Purchase Order PDF.',
                ]);
            }

            return $pdfPath;
        } catch (\Throwable $exception) {
            if (is_file($pdfPath)) {
                @unlink($pdfPath);
            }

            throw $exception;
        } finally {
            if (is_file($htmlPath)) {
                @unlink($htmlPath);
            }

            $this->removeDirectory($profileDirectory);
        }
    }

    private function resolveBinary(): string
    {
        $configured = config('pdf_renderer.chrome_binary');
        $candidates = array_values(array_filter([
            is_string($configured) ? trim($configured) : null,
            'google-chrome',
            'google-chrome-stable',
            'chromium',
            'chromium-browser',
            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
                : null,
            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe'
                : null,
        ]));
        $finder = new ExecutableFinder();

        foreach ($candidates as $candidate) {
            if (
                is_file($candidate) &&
                (
                    PHP_OS_FAMILY === 'Windows' ||
                    is_executable($candidate)
                )
            ) {
                return $candidate;
            }

            $found = $finder->find($candidate);

            if (is_string($found) && $found !== '') {
                return $found;
            }
        }

        throw ValidationException::withMessages([
            'pdf_generation' =>
                'Chrome or Chromium was not found. Configure CHROME_BINARY before importing a Purchase Order.',
        ]);
    }

    private function temporaryDirectory(): string
    {
        $directory = (string) config(
            'pdf_renderer.temporary_directory',
            storage_path('app/private/tmp/po-render')
        );

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0775, true) &&
            !is_dir($directory)
        ) {
            throw ValidationException::withMessages([
                'pdf_generation' =>
                    'The PDF rendering temporary directory could not be created.',
            ]);
        }

        if (!is_writable($directory)) {
            throw ValidationException::withMessages([
                'pdf_generation' =>
                    'The PDF rendering temporary directory is not writable.',
            ]);
        }

        return rtrim(
            $directory,
            DIRECTORY_SEPARATOR
        );
    }

    private function fileUri(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (preg_match('/^[A-Za-z]:\//', $normalized) === 1) {
            return 'file:///' . str_replace(' ', '%20', $normalized);
        }

        return 'file://' . str_replace(' ', '%20', $normalized);
    }
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $entries = scandir($directory);

        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

}