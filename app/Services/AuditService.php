<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(

        string $action,

        string $tableName,

        int $tableId,

        ?array $oldValues = null,

        ?array $newValues = null

    ): void {

        AuditLog::create([

            'user_id' => auth()->id(),

            'table_name' => $tableName,

            'table_id' => $tableId,

            'action' => $action,

            'old_values' => $oldValues,

            'new_values' => $newValues,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

        ]);
    }
}