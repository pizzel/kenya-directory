<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ItineraryStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location_name',
        'business_id',
        'county_id',
        'image_url',
        'order_index'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // --- RELATIONSHIPS ---

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    // --- HELPERS ---

    public function getIsCompletedAttribute()
    {
        // If end_time exists, check that. Else check start_time + 24hrs? Or just start_time.
        $checkDate = $this->end_time ?? $this->start_time;
        return $checkDate->isPast();
    }

    public function getIsHappeningNowAttribute()
    {
        $now = Carbon::now();
        if ($this->end_time) {
            return $now->between($this->start_time, $this->end_time);
        }
        // If no end time, assume it's "now" if it's the same day
        return $now->isSameDay($this->start_time);
    }

    /**
     * Smartly gets the image URL:
     * 1. Custom uploaded image
     * 2. Business Hero Image
     * 3. County Listing Image
     * 4. Placeholder
     */
    public function getDisplayImageAttribute()
    {
        if ($this->image_url) {
            return asset($this->image_url);
        }
        if ($this->business && method_exists($this->business, 'getImageUrl')) {
            return $this->business->getImageUrl('card');
        }
        // Fallback placeholder
        return asset('images/placeholder-card.jpg');
    }
}
