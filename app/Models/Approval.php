<?php

// app/Models/Approval.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'status',
        'signed_at',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}