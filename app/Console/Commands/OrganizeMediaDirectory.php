<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Business;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class OrganizeMediaDirectory extends Command
{
    protected $signature = 'media:organize-business-images';
    protected $description = 'Moves business images from root folders to grouped business folders';

    public function handle()
    {
        $this->info("Starting reorganization of Business Media files...");
        
        // Ensure the destination 'businesses' directory exists in public disk
        $disk = Storage::disk('public');
        
        // Find all media belonging to Businesses
        $query = Media::where('model_type', Business::class)
                      ->orWhere('model_type', 'App\Models\Business');
        
        $count = $query->count();
        $this->info("Found {$count} media items to process.");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($query->cursor() as $media) {
            // The default path is usually based on ID (e.g., '10/')
            // We want to move it to 'businesses/{business_id}/{media_id}/'
            
            $oldRelativePath = $media->id; // The folder name is just the ID in the root
            $newRelativePath = 'businesses/' . $media->model_id . '/' . $media->id;

            // Check if source exists
            if ($disk->exists($oldRelativePath)) {
                // Check if destination already exists (maybe we ran this partially?)
                if ($disk->exists($newRelativePath)) {
                    $this->warn("\nTarget directory {$newRelativePath} already exists. Skipping move for Media {$media->id}.");
                } else {
                    // Create the parent directory for the new location if needed (businesses/{id})
                    // Storage::move automatically handles directory creation usually, but let's be safe regarding the 'businesses/' part.
                    
                    try {
                        // Move the entire directory
                        $disk->move($oldRelativePath, $newRelativePath);
                    } catch (\Exception $e) {
                        $this->error("\nFailed to move Media {$media->id}: " . $e->getMessage());
                    }
                }
            } else {
                // It might already be moved or missing
                if (!$disk->exists($newRelativePath)) {
                    // Only warn if it's not in the new place either
                   // $this->warn("\nSource directory {$oldRelativePath} not found for Media {$media->id}");
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nOrganization complete! Your files are now in storage/app/public/businesses/");
        $this->info("IMPORTANT: Make sure you have updated config/media-library.php to use the BusinessPathGenerator!");
    }
}
