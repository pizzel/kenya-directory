<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MigrateOldEventImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:migrate-events-old';

    /**
     * The console command description.
     */
    protected $description = 'Migrates images from the old event_images table to the new Spatie Media Library system.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // <<< FIX: Changed .$this. to $this-> >>>
        $this->info("Starting migration of old event images to the new media library...");

        // Find all events that have images in the old system.
        $eventsWithOldImages = Event::whereHas('images')->with('images')->get();

        if ($eventsWithOldImages->isEmpty()) {
            // <<< FIX: Changed .$this. to $this-> >>>
            $this->warn('No events with old images found. Nothing to migrate.');
            return 0;
        }

        // <<< FIX: Changed .$this. to $this-> >>>
        $this->info("Found {$eventsWithOldImages->count()} events with images to migrate.");
        $progressBar = $this->output->createProgressBar($eventsWithOldImages->count());

        $progressBar->start();

        foreach ($eventsWithOldImages as $event) {
            $event->clearMediaCollection('images');

            foreach ($event->images as $oldImage) {
                $filePath = storage_path('app/public/' . $oldImage->file_path);

                if (file_exists($filePath)) {
                    try {
                        $event->addMedia($filePath)
                            ->preservingOriginal()
                            ->withCustomProperties(['caption' => $oldImage->caption])
                            ->setOrder($oldImage->order ?? $oldImage->id)
                            ->toMediaCollection('images');
                    } catch (FileDoesNotExist $e) {
                        // <<< FIX: Changed .$this. to $this-> >>>
                        $this->error("\nFile does not exist for event {$event->id}: {$filePath}");
                    } catch (FileIsTooBig $e) {
                        // <<< FIX: Changed .$this. to $this-> >>>
                        $this->error("\nFile is too big for event {$event->id}: {$filePath}");
                    } catch (\Exception $e) {
                        // <<< FIX: Changed .$this. to $this-> >>>
                        $this->error("\nAn unknown error occurred for event {$event->id}: " . $e->getMessage());
                    }
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        
        // <<< FIX: Changed .$this. to $this-> >>>
        $this->info("\n\nEvent image migration complete!");
        $this->info("Please run 'php artisan images:generate-all-conversions' to create thumbnails for these newly migrated images.");

        return 0;
    }
}