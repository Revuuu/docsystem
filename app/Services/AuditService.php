<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public static function log(
        string $action,
        string $tableName,
        int $tableId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?string $event = null,
        ?array $metadata = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),

                'event' => $event,
                'table_name' => $tableName,
                'table_id' => $tableId,
                'action' => strtolower($action),
                'description' => $description,

                'old_values' => self::cleanSensitiveData($oldValues),
                'new_values' => self::cleanSensitiveData($newValues),
                'metadata' => self::cleanSensitiveData($metadata),

                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit log failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function activity(
        string $event,
        string $description,
        ?string $tableName = null,
        ?int $tableId = null,
        string $action = 'activity',
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): void {
        self::log(
            action: $action,
            tableName: $tableName ?? 'system',
            tableId: $tableId ?? 0,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description,
            event: $event,
            metadata: $metadata
        );
    }

    private static function cleanSensitiveData(?array $data): ?array
    {
        if (!$data) {
            return $data;
        }

        $hiddenFields = [
            'password',
            'password_confirmation',
            'current_password',
            'remember_token',
            'signature_data',
        ];

        foreach ($hiddenFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '[hidden]';
            }
        }

        return $data;
    }
}