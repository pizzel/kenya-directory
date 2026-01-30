<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class HealPostCoverImages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'posts:heal-covers';

    /**
     * The console command description.
     */
    protected $description = 'Updates the featured_image_url for all posts based on their new media library content.';

    /**
     * Execute the console command.
     */
   public function handle(): int
{
    $this->info('Starting to heal post cover images...');

    $posts = Post::all();
    $progressBar = $this->output->createProgressBar($posts->count());
    $healedCount = 0;

    foreach ($posts as $post) {
        $newCoverPath = null;
        $contentBlocks = is_array($post->content) ? $post->content : [];

        $firstBusinessBlock = \Illuminate\Support\Arr::first($contentBlocks, function ($block) {
            return isset($block['type'], $block['data']['business_id']) && $block['type'] === 'business_block';
        });

        if ($firstBusinessBlock) {
            $businessId = $firstBusinessBlock['data']['business_id'];
            $business = \App\Models\Business::find($businessId);

            if ($business) {
                $mainImage = $business->getFirstMedia('images');
                if ($mainImage) {
                    // <<< THE DEFINITIVE FIX >>>
                    // 1. Get the path of the 'card' conversion RELATIVE to the public disk root.
                    $newCoverPath = $mainImage->getPath('card'); 
                }
            }
        }
        
        // Only update if the path has changed.
        if ($newCoverPath && $post->featured_image_url !== $newCoverPath) {
            $post->featured_image_url = $newCoverPath;
            $post->save();
            $healedCount++;
        }
        $progressBar->advance();
    }

    $progressBar->finish();
    $this->info("\n\nProcess complete. Healed the cover image for {$healedCount} posts.");

    return 0;
}
}