<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\Conversions\FileManipulator;

class GenerateMainConversionsCommand extends Command
{
    protected $signature = 'images:generate-main-conversions';
    protected $description = 'Efficiently generates all necessary conversions ONLY for the main image of each business.';

    public function handle(): int
    {
        $this->info('Finding the main image for each business and generating conversions...');

        $businesses = Business::has('media')->get();
        $progressBar = $this->output->createProgressBar($businesses->count());
        $fileManipulator = app(FileManipulator::class);
        $generatedCount = 0;

        foreach ($businesses as $business) {
            $mainImage = $business->getFirstMedia('images');
            if ($mainImage) {
                try {
                    // This generates ALL conversions defined in your Business model, but only for this one image.
                    $fileManipulator->createDerivedFiles($mainImage);
                    $generatedCount++;
                } catch (\Exception $e) {
                    $this->error("\nCould not process media for business #{$business->id}: {$e->getMessage()}");
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n\nProcess complete. Generated conversions for the main image of {$generatedCount} businesses.");

        return 0;
    }
}