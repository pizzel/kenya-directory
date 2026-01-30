<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'theme_color',
        'visibility',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // --- RELATIONSHIPS ---

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stops()
    {
        return $this->hasMany(ItineraryStop::class)->orderBy('start_time', 'asc');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'itinerary_participants')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'itinerary_likes')->withTimestamps();
    }

    // --- HELPERS ---

    public function getStatusAttribute()
    {
        $now = Carbon::now();
        if ($this->end_date && $this->end_date->isPast()) {
            return 'completed';
        }
        if ($this->start_date && $this->start_date->isFuture()) {
            return 'upcoming';
        }
        return 'active'; // Currently happening
    }

    public function getDurationStringAttribute()
    {
        if (!$this->start_date || !$this->end_date) return 'Flexible';
        return $this->start_date->diffInDays($this->end_date) . ' Days';
    }

    /**
     * Smartly gets the itinerary cover image:
     * 1. Manual cover_image
     * 2. Image from the first stop's business
     * 3. Fallback pattern
     */
    public function getDisplayImageAttribute()
    {
        if ($this->cover_image) {
            return asset($this->cover_image);
        }

        $firstStop = $this->stops->first();
        if ($firstStop && $firstStop->display_image) {
            return $firstStop->display_image;
        }

        return asset('images/placeholder-card.jpg');
    }
}
