<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class SignatureTemplateResolver
{
    /**
     * Resolve the uploaded PDF against the supported templates.
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

        $uploadedHash = hash_file(
            'sha256',
            $realPath
        );

        if ($uploadedHash === false) {
            return null;
        }

        $uploadedHash = strtolower($uploadedHash);

        foreach (config('signature_templates', []) as $key => $template) {
            $configuredHashes = $this->normalizeHashes(
                $template['hashes'] ?? []
            );

            foreach ($configuredHashes as $configuredHash) {
                if (!hash_equals($configuredHash, $uploadedHash)) {
                    continue;
                }

                return array_merge(
                    $template,
                    [
                        'key' => $template['key'] ?? $key,
                        'uploaded_hash' => $uploadedHash,
                    ]
                );
            }
        }

        return null;
    }

    /**
     * Remove blank or invalid hashes and normalize them to lowercase.
     */
    private function normalizeHashes(array $hashes): array
    {
        return array_values(
            array_filter(
                array_map(
                    static function ($hash): ?string {
                        if (!is_string($hash)) {
                            return null;
                        }

                        $hash = strtolower(trim($hash));

                        if (
                            !preg_match(
                                '/^[a-f0-9]{64}$/',
                                $hash
                            )
                        ) {
                            return null;
                        }

                        return $hash;
                    },
                    $hashes
                )
            )
        );
    }
}