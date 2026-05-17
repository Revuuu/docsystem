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
    'step_order',
    'remarks',
    'rejected_at',
    'sig_x',
    'sig_y',
    'sig_w',
    'sig_h',
    'sig_page',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}