<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DocumentFile;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'uploaded_by',
    'status',
    'admin_signed_at',
];
protected $casts = [
    'admin_signed_at' => 'datetime',
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
    public function files()
{
    return $this->hasMany(DocumentFile::class);
}

public function currentFile()
{
    return $this->hasOne(DocumentFile::class)
        ->where('is_current', true);
}
public function getCurrentFilePathAttribute()
{
    return $this->currentFile?->file_path;
}

public function getCurrentSignedFileAttribute()
{
    return $this->files()
        ->where('is_signed', true)
        ->latest('version')
        ->first();
}
public function latestVersion()
{
    return $this->files()
        ->orderByDesc('version')
        ->first();
}
}