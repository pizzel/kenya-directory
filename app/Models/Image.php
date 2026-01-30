<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model // Or BusinessImage extends Model
{
    use HasFactory, SoftDeletes; // <<< ADD SoftDeletes HERE

    protected $fillable = [
        'business_id', 'file_path', 'caption', 'is_main_gallery_image', 'gallery_order'
    ];

    protected $casts = [
        'is_main_gallery_image' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the publicly accessible URL for the image.
     */
    public function getUrlAttribute(): ?string // Allow null if file_path is somehow null
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null; // Or a default placeholder image URL
    }
}