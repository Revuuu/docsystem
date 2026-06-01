<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
    'user_id',
    'event',
    'table_name',
    'table_id',
    'action',
    'description',
    'old_values',
    'new_values',
    'metadata',
    'ip_address',
    'user_agent',
];

protected $casts = [
    'old_values' => 'array',
    'new_values' => 'array',
    'metadata' => 'array',
];
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}