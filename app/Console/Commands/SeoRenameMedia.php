<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Business;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SeoRenameMedia extends Command
{
    protected $signature = 'media:seo-rename {--force : Actually rename the files}';
    protected $description = 'Renames all existing business images to an SEO-friendly format (name-county-id)';

    public function handle()
    {
        $force = $this->option('force');

        if (!$force) {
            $this->warn("⚠️  DRY RUN MODE: No files will be renamed. Use --force to apply changes.");
        }

        $this->info("Starting SEO renaming for Business Media...");
        
        // Disable conversions during renaming to keep it fast
        Business::$skipMediaConversions = true;

        Media::where('model_type', Business::class)
            ->where('collection_name', 'images')
            ->chunkById(100, function ($mediaItems) use ($force) {
                foreach ($mediaItems as $media) {
                    $business = $media->model;
                    
                    if (!$business) {
                        $this->warn("Skipping Media ID {$media->id}: No associated business found.");
                        continue;
                    }

                    $countyName = $business->county->name ?? '';
                    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                    
                    // Generate new SEO name: name-county-uniqueid
                    $nameSlug = Str::slug($business->name . '-' . $countyName);
                    $newFileName = $nameSlug . '-' . uniqid() . '.' . $extension;

                    if ($media->file_name === $newFileName) {
                        continue;
                    }

                    $oldPath = $media->getPath();
                    $this->line("Renaming: {$media->file_name} -> {$newFileName}");

                    if ($force) {
                        try {
                            // Part 1: Rename the physical file
                            $dir = dirname($oldPath);
                            $newPath = $dir . DIRECTORY_SEPARATOR . $newFileName;

                            if (File::exists($oldPath)) {
                                File::move($oldPath, $newPath);
                                
                                // Part 2: Update the Database
                                $media->file_name = $newFileName;
                                $media->name = $nameSlug; // Update the display name too
                                $media->save();
                                
                                $this->info("✔ Success: " . $newFileName);
                            } else {
                                $this->error("✘ Error: Original file not found at " . $oldPath);
                            }
                        } catch (\Exception $e) {
                            $this->error("✘ Error renaming ID {$media->id}: " . $e->getMessage());
                        }
                    }
                }
            });

        $this->info("SEO Renaming Complete!");
        
        if (!$force) {
            $this->comment("Run with --force to actually apply these changes.");
        }
        
        return 0;
    }
}
