<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder; // For scope
use App\Models\User;
use App\Models\EventReview;
use Carbon\Carbon; // For date comparisons
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia; // This trait provides the missing methods
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'business_id', 'user_id', 'title', 'slug', 'description',
        'county_id', 'address', 'latitude', 'longitude',
        'start_datetime', 'end_datetime', 'is_free', 'price', 'ticketing_url', 'status',
        'average_rating', 
        'reviews_count',
		    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'average_rating' => 'float',   
        'reviews_count' => 'integer', 
       'views_count' => 'integer',
    ];
          public function getImageUrl(string $conversionName = 'card'): string // Default to 'card' for safety
        {
            // The model's internal cache for efficiency
            if (isset($this->mainMediaCache)) {
                $mainMedia = $this->mainMediaCache;
            } else {
                $mainMedia = $this->getFirstMedia('images');
                $this->mainMediaCache = $mainMedia;
            }

            if ($mainMedia) {
                // If an empty string is passed as the conversion name, it means our script
                // wants the raw, relative file path for saving to the database.
                // The getPath() method on a media item returns this.
                if ($conversionName === '') {
                    return $mainMedia->getPath();
                }
                
                // For all other cases (like in Blade views), return the full, public URL
                // for the specified conversion (e.g., 'card', 'hero').
                return $mainMedia->getUrl($conversionName);
            }

            // Return a placeholder if no image exists.
            return asset('images/placeholder-card.jpg');
        }
     public function registerMediaConversions(Media $media = null): void
            {
                // We can use the same conversion names for consistency
                $this->addMediaConversion('card')
                    ->width(800)
                    ->height(600)
                    ->format('jpg')
                    ->quality(85);

                $this->addMediaConversion('thumbnail')
                    ->width(400)
                    ->height(300)
                    ->format('jpg')
                    ->quality(85);
            }
    public function business() { return $this->belongsTo(Business::class); }
    public function user() { return $this->belongsTo(User::class); } // Creator
    public function county() { return $this->belongsTo(County::class); }
    public function categories() { return $this->belongsToMany(EventCategory::class, 'event_event_category'); }
    public function images() { return $this->hasMany(EventImage::class); }

    // Scope for active and upcoming events
    public function scopeActiveUpcoming(Builder $query): Builder {
        return $query->where('status', 'active')->where('end_datetime', '>=', now());
    }
	public function scopeActiveAndUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where('end_datetime', '>=', now());
    }

    /**
     * Scope a query to only include past events.
     * (Either status is 'past' OR end_datetime is in the past for active/pending events)
     */
    public function scopePastEvents(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'past')
              ->orWhere('end_datetime', '<', now()); // Include active/pending events whose end_datetime has passed
        })
        // Optionally exclude cancelled events from "past" if you want cancelled to be a distinct category
        ->where('status', '!=', 'cancelled');
    }

   
	public function scopeWithAllPublicEventRelations(Builder $query) {
		return $query->with([
			'business:id,name,slug', 'county:id,name,slug', 'categories:id,name,slug',
			'images' => fn($q) => $q->where('is_main_event_image', true)->limit(1)
		])->withCount('reviews')->withAvg('reviews', 'rating'); // Assuming events have reviews
	}
	
     public function reviews() {
        return $this->hasMany(EventReview::class);
    }
	
    public function getDisplayStatusAttribute(): string
    {
        // Ensure end_datetime is a Carbon instance
        $endDateTime = ($this->end_datetime instanceof Carbon) ? $this->end_datetime : Carbon::parse($this->end_datetime);

        if ($this->status === 'cancelled') {
            return 'cancelled';
        }
        if ($endDateTime && $endDateTime->isPast()) {
            return 'past'; // Override if end_datetime has passed
        }
        return $this->status; // Return the database status otherwise
    }

    // Helper to update average rating and review count for events
     public function updateAverageRatingAndReviewCount() {
        $this->reviews_count = $this->reviews()->count(); // Count all reviews for this event
        $this->average_rating = $this->reviews_count > 0 ? $this->reviews()->avg('rating') : null;
        $this->saveQuietly();
    }
	
	public function reports() {
    return $this->hasMany(Report::class);
	}
}