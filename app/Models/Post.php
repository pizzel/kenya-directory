<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr; // Import the Arr helper

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'excerpt', 'featured_image_url',
        'status', 'meta_description', 'meta_keywords', 'published_at','views',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'meta_keywords' => 'array',
        'content' => 'array', // <<< THIS IS THE CRITICAL CHANGE
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	public function likers()
		{
			return $this->belongsToMany(User::class, 'likes');
		}

    // This relationship is no longer needed as businesses are part of the content.
    // public function businesses() { ... } 

	protected static function booted(): void
    {
        static::saving(function (Post $post) {
            // 1. If an image was already set by the Command or Manual Upload, do nothing.
            if ($post->isDirty('featured_image_url') && !empty($post->featured_image_url)) {
                return;
            }

            // 2. Safety Net: If the cover image is empty, try to extract one from the featured businesses.
            if (empty($post->featured_image_url)) {
                $contentBlocks = $post->content ?? [];
                
                // Find the first business block in the JSON content
                $firstBusinessBlock = \Illuminate\Support\Arr::first($contentBlocks, function ($block) {
                    return isset($block['type'], $block['data']['business_id']) && $block['type'] === 'business_block';
                });

                if ($firstBusinessBlock) {
                    $businessId = $firstBusinessBlock['data']['business_id'];
                    
                    // Fetch the business and its Spatie Media
                    $business = \App\Models\Business::with('media')->find($businessId);

                    if ($business) {
                        // Look for the first image in the 'images' collection
                        $mediaItem = $business->getFirstMedia('images');
                        
                        if ($mediaItem) {
                            // Save the path relative to root (compatible with your existing Storage::url() calls)
                            $post->featured_image_url = $mediaItem->getPathRelativeToRoot();
                        }
                    }
                }
            }
        });
    }
}