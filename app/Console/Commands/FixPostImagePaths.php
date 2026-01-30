<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Post;
use Illuminate\Console\Command;

class FixPostImagePaths extends Command
{
    protected $signature = 'posts:fix-image-paths';
    protected $description = 'Scans old blog posts and updates their featured_image_url to use the new media library conversions.';

    public function handle(): int
    {
        $this->info("Starting to find and fix old post image paths...");

        // Find all posts where the featured_image_url still points to the old 'businesses/' directory.
        $postsToFix = Post::where('featured_image_url', 'like', 'businesses/%')->get();

        if ($postsToFix->isEmpty()) {
            $this->info("No posts found with old image paths. All set!");
            return 0;
        }

        $this->info("Found {$postsToFix->count()} posts with outdated image paths to fix.");
        $progressBar = $this->output->createProgressBar($postsToFix->count());
        $fixedCount = 0;

        $progressBar->start();

        foreach ($postsToFix as $post) {
            // The old path looks like: "businesses/85/tgrv-circuit-1751917100-8.jpg"
            // We need to extract the business ID (the number after 'businesses/').
            if (preg_match('/\/home\/discove6\/Discover_Kenya\/storage\/app\/public\//', $post->featured_image_url, $matches)) {
                $businessId = (int) $matches[1];
                
                // Now, find the business with that ID.
                $business = Business::find($businessId);

                if ($business) {
                    // Get the new, correct 'card' conversion URL using our helper.
                    // IMPORTANT: We use the file_path, not the full URL, to be consistent.
                    $mainImage = $business->getFirstMedia('images');
                    if ($mainImage) {
                        $newImagePath = $mainImage->getPath('card'); // Get the path to the conversion
                        
                        // We need the path relative to the storage/app/public directory
                        $relativePath = str_replace(storage_path('app/public') . '/', '', $newImagePath);

                        // Update the post with the new path.
                        $post->featured_image_url = $relativePath;
                        $post->save();
                        $fixedCount++;
                    }
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\nProcess complete. Successfully fixed image paths for {$fixedCount} posts.");

        return 0;
    }
}