<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    protected $fillable = [

        'document_id',
        'parent_file_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'version',
        'is_signed',
        'is_current',
        'uploaded_by',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    public function parent()
    {
        return $this->belongsTo(DocumentFile::class, 'parent_file_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentFile::class, 'parent_file_id');
    }
}