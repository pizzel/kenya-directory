<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SetPostCoverImageCommand extends Command
{
    /**
     * The signature of the console command. Takes a Post ID as an argument.
     */
    protected $signature = 'posts:set-cover {post_id}';

    /**
     * The console command description.
     */
    protected $description = 'Sets the featured image for a specific post based on the first featured business in its content.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $postId = $this->argument('post_id');

        try {
            $post = Post::findOrFail($postId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("No post found with ID: {$postId}");
            return 1;
        }

        $this->info("Processing post: '{$post->title}' (ID: {$post->id})");

        $contentBlocks = $post->content ?? [];
        if (!is_array($contentBlocks)) {
            $this->error('Post content is not in the expected format. Cannot find featured businesses.');
            return 1;
        }

        // Find the first block that is a 'business_block' and has a business_id.
        $firstBusinessBlock = Arr::first($contentBlocks, function ($block) {
            return isset($block['type'], $block['data']['business_id']) && $block['type'] === 'business_block';
        });

        if (!$firstBusinessBlock) {
            $this->warn("No featured businesses found in the content for this post. Cannot set a cover image.");
            return 1;
        }

        $businessId = $firstBusinessBlock['data']['business_id'];
        $business = Business::find($businessId);

        if (!$business) {
            $this->error("Could not find the featured business with ID: {$businessId}");
            return 1;
        }

        // Get the main image from the new media library
        $mainImage = $business->getFirstMedia('images');

        if (!$mainImage) {
            $this->warn("The featured business '{$business->name}' does not have a main image. Cannot set cover.");
            return 1;
        }

        // Get the relative path of the 'card' conversion
        $newImagePath = $mainImage->getPath('card');
        $relativePath = str_replace(storage_path('app/public') . '/', '', $newImagePath);

        // Update the post record
        $post->featured_image_url = $relativePath;
        $post->save();

        $this->info("-> Successfully set the featured image to: {$relativePath}");
        $this->info("-> From business: '{$business->name}'");

        return 0;
    }
}