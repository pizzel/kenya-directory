<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Business;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixBlogImagesCommand extends Command
{
    protected $signature = 'blog:fix-images';
    protected $description = 'Fixes blog post cover images by ensuring business media exists and linking to card conversion';

    public function handle()
    {
        $this->info("Starting Blog Image Fix...");

        $posts = Post::all();
        $manipulator = app(FileManipulator::class);
        $bar = $this->output->createProgressBar($posts->count());

        foreach ($posts as $post) {
            $contentBlocks = $post->content ?? [];
            if (is_string($contentBlocks)) {
                $contentBlocks = json_decode($contentBlocks, true) ?? [];
            }

            // Find the first business block
            $firstBusinessBlock = collect($contentBlocks)->first(function ($block) {
                return isset($block['type'], $block['data']['business_id']) && $block['type'] === 'business_block';
            });

            if (!$firstBusinessBlock) {
                $bar->advance();
                continue;
            }

            $businessId = $firstBusinessBlock['data']['business_id'];
            $business = Business::with('media')->find($businessId);

            if (!$business) {
                $bar->advance();
                continue;
            }

            // Get first image
            $media = $business->getFirstMedia('images');
            if (!$media) {
                $this->warn("\nNo media found for Business ID: {$businessId} (Post: {$post->title})");
                $bar->advance();
                continue;
            }

            // check if file exists on disk as per DB
            if (!file_exists($media->getPath())) {
                $this->comment("\nMedia file missing for ID {$media->id}. searching directory...");
                
                $dir = dirname($media->getPath());
                if (is_dir($dir)) {
                    $files = glob($dir . '/*');
                    // filter out directories (like conversions)
                    $files = array_filter($files, 'is_file');
                    
                    if (count($files) > 0) {
                        // Assume first file is the one
                        $actualFile = reset($files);
                        $filename = basename($actualFile);
                        
                        $this->info("Found file: {$filename}. Updating Media record.");
                        $media->file_name = $filename;
                        $media->save();
                    } else {
                        $this->error("Directory empty or no files found: {$dir}");
                        $bar->advance();
                        continue;
                    }
                } else {
                     $this->error("Directory missing: {$dir}");
                     $bar->advance();
                     continue;
                }
            }

            // Ensure 'card' conversion exists
            $cardPath = $media->getPath('card');
            if (!file_exists($cardPath)) {
                $this->comment("Generating 'card' conversion for Media {$media->id}...");
                try {
                    $manipulator->createDerivedFiles($media, ['card']);
                } catch (\Exception $e) {
                    $this->error("Failed to generate card: " . $e->getMessage());
                }
            }

            // Update Post
            // We need the relative path for Storage::url()
            // e.g. 263/conversions/filename-card.jpg
            
            // Refetch media to be sure (though object matches)
            // recalculate path
             // Spatie path generator standard: {id}/conversions/{name}-{conversion}.{ext}
            $conversionFileName = pathinfo($media->file_name, PATHINFO_FILENAME) . '-card.jpg';
            // Verify ext
            // createDerivedFiles might use original ext or jpg. Let's check what exists.
            
            // actually $media->getPath('card') gives full path.
            $fullCardPath = $media->getPath('card');
            
            if (file_exists($fullCardPath)) {
                 // Convert full path to relative public path
                 // C:\xampp\htdocs\...\storage\app\public\263\...\
                 // We want 263/conversions/...
                 
                 // relative path from storage/app/public
                 $relativePath = Str::after($fullCardPath, 'public' . DIRECTORY_SEPARATOR); 
                 // Handle windows backslashes
                 $relativePath = str_replace('\\', '/', $relativePath);
                 // remove leading slash if any
                 $relativePath = ltrim($relativePath, '/');

                 if ($post->featured_image_url !== $relativePath) {
                     $post->featured_image_url = $relativePath;
                     $post->saveQuietly(); // Skip model events
                     // $this->info("Updated Post {$post->id} image URL.");
                 }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nBlog images fixing completed.");
    }
}
