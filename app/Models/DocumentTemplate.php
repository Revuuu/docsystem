<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_key',
        'name',
        'version',
        'file_path',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * User who originally created this template version.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * User who last modified this template version.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /**
     * Filter active template versions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter templates using a specific template key.
     */
    public function scopeForKey(
        Builder $query,
        string $templateKey
    ): Builder {
        return $query->where(
            'template_key',
            $templateKey
        );
    }
}