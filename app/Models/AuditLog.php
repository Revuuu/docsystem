<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [

        'user_id',

        'table_name',

        'table_id',

        'action',

        'old_values',

        'new_values',

        'ip_address',

        'user_agent',

    ];

    protected $casts = [

        'old_values' => 'array',

        'new_values' => 'array',

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