<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateLegacyImages extends Command
{
    protected $signature = 'images:migrate-legacy';
    protected $description = 'Migrate images from legacy images table to Spatie Media Library';

    public function handle()
    {
        $this->info("Starting migration of legacy images (Conversions DISABLED for speed)...");
        Business::$skipMediaConversions = true;

        Image::chunkById(100, function ($legacyImages) {
            foreach ($legacyImages as $legacyImage) {
                $business = $legacyImage->business;

                if (!$business) {
                    $this->warn("No business found for image ID: {$legacyImage->id}. Skipping.");
                    continue;
                }

                $oldPath = $legacyImage->file_path;
                $fullPath = Storage::disk('public')->path($oldPath);

                if (file_exists($fullPath)) {
                    try {
                        $countyName = $business->county->name ?? '';
                        $filename = Str::slug($business->name . '-' . $countyName . '-' . uniqid()) . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

                        // using preservingOriginal(false) will MOVE the file, making it much faster
                        $business->addMedia($fullPath)
                            ->preservingOriginal(false) 
                            ->usingFileName($filename)
                            ->toMediaCollection('images');

                        $this->line("Migrated & Moved: " . $oldPath);
                        
                        // Delete the record since we moved the file
                        $legacyImage->forceDelete();
                    } catch (\Exception $e) {
                        $this->error("Failed to migrate " . $oldPath . ": " . $e->getMessage());
                    }
                } else {
                    $this->warn("File not found on disk: " . $fullPath);
                    $legacyImage->forceDelete(); // Clean up DB record if file is missing
                }
            }
        });

        $this->info("\nMigration complete!");
        return 0;
    }
}
