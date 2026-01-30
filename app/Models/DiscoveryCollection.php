<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DiscoveryCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'cover_image_url', 'is_active', 'display_order'
    ];

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_discovery_collection');
    }

    /**
     * Optimized relationship to get just one business for the cover image.
     */
    public function coverBusiness()
    {
        return $this->belongsToMany(Business::class, 'business_discovery_collection')
            ->limit(1);
    }

    /**
     * Helper to get the correct URL for the cover image.
     * This handles both manual uploads and auto-assigned paths from businesses.
     */
    public function getCoverImageUrl(string $conversion = 'thumbnail'): string
    {
        // 1. If a specific cover image is manually set in the DB
        if ($this->cover_image_url) {
            // ROBUSTNESS FIX: Only use it if the file actually exists
            // This prevents broken images if files were deleted or not migrated
            if (Storage::disk('public')->exists($this->cover_image_url)) {
                return Storage::disk('public')->url($this->cover_image_url);
            }
        }

        // 2. Optimized Fallback: Use the eager-loaded coverBusiness if available
        $firstBusiness = $this->relationLoaded('coverBusiness') 
            ? $this->coverBusiness->first() 
            : ($this->relationLoaded('businesses') ? $this->businesses->first() : $this->businesses()->first());

        if (!$firstBusiness) {
            return asset('images/placeholder-large.jpg');
        }

        return $firstBusiness->getImageUrl($conversion);
    }
}