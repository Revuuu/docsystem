<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentMessage extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'message',
        'attachment'
    ];

    protected function casts(): array
    {
        return [
            'attachment' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
