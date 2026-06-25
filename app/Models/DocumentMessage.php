<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentMessage extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
