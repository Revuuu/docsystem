<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'file_path',
    'signed_file_path',
    'uploaded_by',
    'status',
    'admin_signed_at',
];

    // Relationship to get uploader user
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Optional: relationship to approvals
    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
}