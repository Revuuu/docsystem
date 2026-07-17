<?php

// app/Models/Approval.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use App\Models\User;

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

    'received_at',
    'completed_at',
    'duration_seconds',

    'sig_x',
    'sig_y',
    'sig_w',
    'sig_h',
    'sig_page',
    ];

    protected $casts = [
    'signed_at' => 'datetime',
    'rejected_at' => 'datetime',
    'received_at' => 'datetime',
    'completed_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function signatureBlock(): HasOne
    {
        return $this->hasOne(
            DocumentSignatureBlock::class,
            'approval_id'
        );
    }
}