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
    
    protected $appends = [
        'view_url',
        'download_url',
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

    public function getGeneratedFileNameAttribute()
{
    $signed = $this->is_signed
        ? '_signed'
        : '';

    return 'document_' .
        $this->document_id .
        '_v' .
        $this->version .
        $signed .
        '.pdf';
}

public function getGeneratedStoragePathAttribute()
{
    return 'document/' .
        $this->generated_file_name;
}

public function getViewUrlAttribute()
{
    return route(
        'files.view',
        encrypt($this->id)
    );
}

public function getDownloadUrlAttribute()
{
    return route(
        'files.download',
        encrypt($this->id)
    );
}
}