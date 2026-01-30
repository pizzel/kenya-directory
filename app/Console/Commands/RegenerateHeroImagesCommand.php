<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Conversions\FileManipulator;

class RegenerateHeroImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:regenerate-hero';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates the "hero" & "hero-mobile" conversions ONLY for businesses currently eligible for the hero slider.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to regenerate hero conversions for eligible businesses...');

        // This now uses the corrected scope in your Business model
        $heroBusinesses = Business::eligibleForHeroSlider()->get();

        if ($heroBusinesses->isEmpty()) {
            $this->warn('No businesses are currently eligible for the hero slider. Nothing to process.');
            return 0;
        }

        $this->info("Found {$heroBusinesses->count()} businesses eligible for the hero slider.");

        // Create a query to find all media items that belong to these specific businesses
        $mediaQuery = Media::query()
            ->where('model_type', Business::class)
            ->whereIn('model_id', $heroBusinesses->pluck('id'));

        $totalMediaCount = $mediaQuery->count();

        if ($totalMediaCount === 0) {
            $this->info('These businesses have no media items to process.');
            return 0;
        }

        $this->info("Found {$totalMediaCount} total media items to regenerate.");

        $progressBar = $this->output->createProgressBar($totalMediaCount);
        $fileManipulator = app(FileManipulator::class);

        // Process the media in chunks for efficiency, eager loading the model relationship
        $mediaQuery->with('model')->chunkById(100, function ($mediaItems) use ($progressBar, $fileManipulator) {
            foreach ($mediaItems as $mediaItem) {
                try {
                    // This will now work correctly because $mediaItem is a full Media object
                    $fileManipulator->createDerivedFiles($mediaItem, ['hero', 'hero-mobile']);
                } catch (\Exception $e) {
                    $this->error("\nCould not process media ID {$mediaItem->id}: {$e->getMessage()}");
                }
                $progressBar->advance();
            }
        });

        // <<< THESE LINES ARE NOW CORRECTLY INSIDE THE handle() METHOD >>>
        $progressBar->finish();
        $this->info("\n\nSuccessfully regenerated hero conversions for the hero slider businesses.");

        return 0;
    }
}