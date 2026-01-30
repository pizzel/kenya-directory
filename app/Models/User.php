<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // If you implement email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel; // Import Panel
use Laravel\Sanctum\HasApiTokens; // For API authentication if needed
use Filament\Models\Contracts\FilamentUser;
use App\Models\Business; // Ensure this is imported
use App\Models\Event;    // <<< ADD THIS IMPORT
use App\Models\Review;
use App\Models\EventReview; // Import

class User extends Authenticatable implements MustVerifyEmail , FilamentUser // Add MustVerifyEmail if you will use it
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',         // Added
        'blocked_at',   // Added
        'google_id', // <-- ADD
        'google_token', // <-- ADD
        'google_refresh_token', // <-- ADD
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ensures password is automatically hashed
        'blocked_at' => 'datetime', // Added
    ];

    // RELATIONSHIPS

    /**
     * Get the businesses owned by the user.
     */
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
	public function posts()
    {
        return $this->hasMany(Post::class);
    }
	
	public function likedPosts()
	{
		return $this->belongsToMany(Post::class, 'likes');
	}
	
	public function likedBusinesses()
    {
        return $this->belongsToMany(Business::class, 'business_likes');
    }	
		
	// In User model
	public function isEditor(): bool
	{
		return $this->role === 'editor';
	}

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
         return $this->isAdmin() || $this->isEditor(); // Or str_ends_with($this->email, '@yourdomain.com') for specific emails
    }
	
	// In app/Models/User.php
	public function wishlistedBusinesses()
{
    return $this->belongsToMany(Business::class, 'wishlists')
                ->withPivot('status') // Important to access the status
                ->withTimestamps();   // If you want created_at/updated_at on pivot
}

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
	
	  public function wishlistedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_wishlists', 'user_id', 'event_id')
                    ->withPivot('status', 'id') // To access status and the pivot record's ID
                    ->withTimestamps();          // To access created_at/updated_at on the pivot table
    }
    public function joinedItineraries()
    {
        return $this->belongsToMany(Itinerary::class, 'itinerary_participants')
                    ->withPivot('status')
                    ->withTimestamps();
    }
    // --- END NEW RELATIONSHIP ---


    /**
     * Get the businesses the user has wishlisted.
     */
  

    // Helper methods for roles (optional but useful)
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBusinessOwner(): bool
    {
        return $this->role === 'business_owner';
    }

    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }
	
	public function isAdminOrEditor(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }
	public function reportsMade()
	{
		return $this->hasMany(Report::class, 'user_id');
	}

	public function reportsReviewed()
	{
		return $this->hasMany(Report::class, 'reviewed_by_admin_id');
	}
	public function eventsCreated() 
	{ 
    return $this->hasMany(Event::class);
	}
	 public function eventReviews() {
        return $this->hasMany(EventReview::class);
    }
	
}