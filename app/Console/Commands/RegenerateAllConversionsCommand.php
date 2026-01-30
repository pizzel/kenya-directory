<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Illuminate\Support\Facades\File;


class RegenerateAllConversionsCommand extends Command
{
    protected $signature = 'images:regenerate-all-conversions {--force}';
    protected $description = 'Deletes ALL existing conversions for ALL media and regenerates them. A long-running process.';

    public function handle(): int
{
    if (!$this->option('force')) {
        if (!$this->confirm('This will delete and regenerate thousands of image files and will take a very long time. Are you absolutely sure you want to continue?')) {
            $this->comment('Operation cancelled.');
            return 0;
        }
    }
    
    $this->info('Starting the full conversion cleanup and regeneration process...');
    $mediaQuery = Media::query();
    $totalMediaCount = $mediaQuery->count();

    if ($totalMediaCount === 0) {
        $this->info('No media found. Nothing to do.');
        return 0;
    }

    $this->info("Found {$totalMediaCount} total media items to process.");
    $progressBar = $this->output->createProgressBar($totalMediaCount);
    $fileManipulator = app(FileManipulator::class);

    $mediaQuery->chunkById(200, function ($mediaItems) use ($progressBar, $fileManipulator) {
        foreach ($mediaItems as $mediaItem) {
            try {
                // <<< START: THE MANUAL, FOOLPROOF FIX >>>

                // Step 1: Manually get the path to the conversions directory.
                $conversionsPath = dirname($mediaItem->getPath());
                $conversionsDirectory = $conversionsPath . '/conversions';

                // Step 2: Check if this directory exists, and if so, delete it completely.
                // File::deleteDirectory() is a safe Laravel helper.
                if (File::isDirectory($conversionsDirectory)) {
                    File::deleteDirectory($conversionsDirectory);
                }
                
                // <<< END: THE MANUAL FIX >>>

                // Step 3: Now, generate a fresh set of all conversions.
                // This part was already working correctly.
                $fileManipulator->createDerivedFiles($mediaItem);

            } catch (\Exception $e) {
                $this->error("\nCould not process media ID {$mediaItem->id}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }
    });

    $progressBar->finish();
    $this->info("\n\nProcess complete. All conversions have been cleaned and regenerated.");

    return 0;
}
}