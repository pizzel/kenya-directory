<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HeroSliderHistory; 
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Business extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    public static $skipMediaConversions = false;

    protected $fillable = [
        'user_id', 'name', 'slug', 'about_us', 'description', 'address',
        'county_id', 'latitude', 'longitude', 'phone_number', 'email',
        'website', 'price_range', 'is_verified', 'status', 'views_count','social_links','min_price',
        'max_price','hero_slider_paid_at','hero_slider_expires_at','is_featured', 
        'featured_expires_at',
        'google_rating', 
        'google_rating_count',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'hero_slider_paid_at' => 'datetime',
        'hero_slider_expires_at' => 'datetime',
        'social_links' => 'array',
        'views_count' => 'integer',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'featured_expires_at' => 'datetime',
    ];

    

    public function registerMediaConversions(Media $media = null): void
    {
        if (self::$skipMediaConversions) {
            return;
        }

            // --- NEW: SURGICAL HOME PAGE OPTIMIZATIONS ---
            // These are ONLY triggered by the rotation command. 
            // They do not affect existing hero images or future uploads' standard conversions.
            $this->addMediaConversion('home-optimized-mobile')
                ->width(800)->height(600)
                ->format('webp')->quality(70); // Low weight, high speed for mobile

            $this->addMediaConversion('home-optimized-desktop')
                ->width(1920)->height(1080) // Kept your preferred resolution
                ->format('webp')->quality(80); // WebP format will still save ~70% space over JPG


            // --- YOUR EXISTING SETTINGS (DO NOT CHANGE) ---
            $this->addMediaConversion('hero-mobile')
                ->width(800)->height(600)
                ->format('webp')->quality(80);

            $this->addMediaConversion('hero')
                ->width(1920)->height(1080)
                ->format('webp')->quality(80);

            $this->addMediaConversion('card')
                ->width(400)->height(300)->sharpen(10)
                ->format('jpg')->quality(90);

            $this->addMediaConversion('thumbnail')
                ->width(150)->height(150)
                ->format('jpg')->quality(90);
        }


    // === RELATIONSHIPS ===

    // <<< ADD THIS NEW RELATIONSHIP >>>
    public function googleReviews()
    {
        // Orders them by 'time' (UNIX timestamp) descending, so newest are first
        return $this->hasMany(GoogleReview::class)->orderBy('time', 'desc');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wishedByUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withPivot('status')->withTimestamps();
    }
    
    public function heroSliderHistories()
    {
         return $this->hasMany(HeroSliderHistory::class)->orderBy('activated_at', 'desc');
    }

    public function discoveryCollections()
    {
        return $this->belongsToMany(DiscoveryCollection::class, 'business_discovery_collection');
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'business_category');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'business_facility');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'business_tag');
    }

    // These are your internal app reviews
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }


    
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'business_post');
    }
    
    public function likers()
    {
        return $this->belongsToMany(User::class, 'business_likes');
    }
    
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // === SCOPES & ACCESSORS ===

    public function scopeEligibleForHeroSlider(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->whereHas('heroSliderHistories', function ($historyQuery) {
                         $historyQuery->where('activated_at', '<=', now())
                                      ->where('set_to_expire_at', '>=', now());
                     })
                     ->whereHas('media', function ($mediaQuery) {
                         $mediaQuery->where('collection_name', 'images');
                     });
    }

    /**
     * Accessor to get the current active hero placement.
     * OPTIMIZED: Uses the loaded relationship to avoid N+1 queries.
     */
    public function getCurrentHeroPlacementAttribute()
    {
        // 1. Check if the relationship is already loaded in memory
        if ($this->relationLoaded('heroSliderHistories')) {
            return $this->heroSliderHistories
                ->where('activated_at', '<=', now())
                ->where('set_to_expire_at', '>=', now())
                ->first();
        }

        // 2. Fallback if not loaded (only happens on single-page loads)
        return $this->heroSliderHistories()
                    ->where('activated_at', '<=', now())
                    ->where('set_to_expire_at', '>=', now())
                    ->first();
    }

    /**
 * Optimized Image retrieval for list views.
 */
public function getImageUrl(string $conversionName = 'thumbnail'): string 
{
    // 1. Get the target media (favoring eager loaded collection)
    // CRITICAL FIX: Must use same sorting as getFirstMedia() to avoid mismatch
    // getFirstMedia() returns the media with the LOWEST order_column value
    $media = $this->relationLoaded('media')
        ? $this->media->where('collection_name', 'images')->sortBy('order_column')->first()
        : $this->getFirstMedia('images');

    if (!$media) {
        return asset('images/placeholder-card.jpg');
    }

    // 2. Try the requested thumbnail first (URL generation is fast)
    // If we just ran a deep clean/regenerate, this is our goal.
    return $media->getUrl($conversionName);
}

    public function updateAverageRating()
    {
        $this->reviews_count_approved = $this->reviews()->count();
        $this->average_rating = $this->reviews_count_approved > 0 ? $this->reviews()->avg('rating') : null;
        $this->saveQuietly();
    }
    
    public function scopeWithAllPublicRelations(Builder $query)
    {
        return $query->with([
            'county',
            'categories',
            // Note: We don't load googleReviews here by default to keep the listing query light. 
            // We will load them only on the 'show' page.
            'reviews' => fn($q) => $q->where('is_approved', true) 
        ])
        ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)]); 
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (Business $business) {
            // Cleanup Spatie Media
            $business->clearMediaCollection('images'); 

        });
    }
}