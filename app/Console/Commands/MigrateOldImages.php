<?php
namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class MigrateOldImages extends Command
{
    protected $signature = 'images:migrate-old';
    protected $description = 'Migrates images from the old system to Spatie Media Library.';

            public function handle()
            {
                // <<< FIX: Changed .$this. to $this-> >>>
                $this->info("Starting migration of old images to the new media library...");
                
                // Get all businesses that have images in the old system
                $businesses = Business::whereHas('images')->with('images')->get();

                // <<< FIX: Changed .$this. to $this-> >>>
                $progressBar = $this->output->createProgressBar($businesses->count());
                
                foreach ($businesses as $business) {
                    foreach ($business->images as $oldImage) {
                        $filePath = storage_path('app/public/' . $oldImage->file_path);
                        
                        if (file_exists($filePath)) {
                            try {
                                $business->addMedia($filePath)
                                    ->preservingOriginal()
                                    ->withCustomProperties(['caption' => $oldImage->caption])
                                    ->toMediaCollection('images');
                            } catch (\Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist $e) {
                                // <<< FIX: Changed .$this. to $this-> >>>
                                $this->error("\nFile does not exist for business {$business->id}: {$filePath}");
                            }
                        }
                    }
                    $progressBar->advance();
                }
                
                $progressBar->finish();
                
                // <<< FIX: Changed .$this. to $this-> >>>
                $this->info("\n\nImage migration complete!");
            }
}