<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RotateMainImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:rotate-main';

    /**
     * The console command description.
     */
    protected $description = 'Randomly selects a NEW main cover image for businesses (guaranteed to be different from current).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to rotate main images for eligible businesses...');

        // 1. Get IDs of currently active Hero Slider businesses to protect them
        $activeHeroBusinessIds = \App\Models\HeroSliderHistory::where('set_to_expire_at', '>', now())
            ->pluck('business_id')
            ->toArray();

        if (count($activeHeroBusinessIds) > 0) {
            $this->info("Skipping " . count($activeHeroBusinessIds) . " active Hero Slider businesses.");
        }

        // 2. Find businesses with > 1 image
        $eligibleBusinesses = Business::whereNotIn('id', $activeHeroBusinessIds)
            ->has('media', '>', 1)
            ->get();

        if ($eligibleBusinesses->isEmpty()) {
            $this->info('No businesses found with more than one image.');
            return 0;
        }

        $this->info("Found {$eligibleBusinesses->count()} businesses to process.");
        $progressBar = $this->output->createProgressBar($eligibleBusinesses->count());
        $rotatedCount = 0;

        $progressBar->start();

        foreach ($eligibleBusinesses as $business) {
            // Get media sorted by current order (Spatie uses 'order_column')
            $mediaItems = $business->getMedia('images')->sortBy('order_column')->values();
            
            if ($mediaItems->count() < 2) {
                $progressBar->advance();
                continue;
            }

            // The image currently at index 0 is the current main image
            $currentMainImageId = $mediaItems->first()->id;

            // Create a pool of candidates that EXCLUDES the current main image
            $candidates = $mediaItems->where('id', '!=', $currentMainImageId);

            // Pick a random new main image from the candidates
            $newMainImage = $candidates->random();

            // Get all IDs except the new main one
            $remainingIds = $mediaItems->pluck('id')
                ->reject(fn ($id) => $id === $newMainImage->id)
                ->shuffle() // Shuffle the rest so the secondary images also change order
                ->toArray();

            // Construct new order: [New Main Image, ...Shuffled Remaining Images]
            $newOrderIds = array_merge([$newMainImage->id], $remainingIds);
            
            Media::setNewOrder($newOrderIds);
            
            $rotatedCount++;
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\nProcess complete.");
        $this->info("Successfully rotated images for {$rotatedCount} businesses.");

        $this->info("\nClearing application cache...");
        $this->call('cache:clear');

        return 0;
    }
}