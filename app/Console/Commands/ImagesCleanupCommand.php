<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ImagesCleanupCommand extends Command
{
    /**
     * The signature allows a --limit and a --dry-run option for safety.
     */
    protected $signature = 'images:cleanup {--limit=10} {--dry-run}';

    protected $description = 'Cleans up duplicate and excess images for all businesses.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("--- DRY RUN MODE: No files or records will be deleted. ---");
        } else {
            if (!$this->confirm("This will permanently delete images and database records. Are you sure you want to continue?")) {
                $this->info("Cleanup cancelled.");
                return 0;
            }
        }

        $this->info("Starting image cleanup process. Enforcing a limit of {$limit} images per business.");

        // Get all businesses that have at least one image to process
        $businesses = Business::has('images')->with('images')->get();
        $progressBar = $this->output->createProgressBar($businesses->count());
        $totalImagesDeleted = 0;
        $totalFilesDeleted = 0;

        $progressBar->start();

        foreach ($businesses as $business) {
            /** @var Collection $images */
            $images = $business->images;
            $imagesToDelete = collect();
            $seenReferences = [];

            // --- Pass 1: Find and mark duplicates for deletion ---
            foreach ($images as $image) {
                // Extract photo reference from caption
                preg_match('/Photo Reference: (.*?)\./', $image->caption ?? '', $matches);
                $photoRef = $matches[1] ?? null;

                // If no reference, we can't check for duplicates, so we keep it.
                // If we've seen this reference before, mark the current image for deletion.
                if ($photoRef && isset($seenReferences[$photoRef])) {
                    $imagesToDelete->push($image);
                } elseif ($photoRef) {
                    $seenReferences[$photoRef] = true;
                }
            }
            
            // --- Pass 2: Enforce the overall limit on the remaining unique images ---
            $remainingImages = $images->diff($imagesToDelete);
            
            if ($remainingImages->count() > $limit) {
                // Sort by gallery order (or ID as a fallback) to keep the first ones
                $sortedImages = $remainingImages->sortBy('gallery_order')->sortBy('id');
                $imagesOverLimit = $sortedImages->slice($limit);
                $imagesToDelete = $imagesToDelete->merge($imagesOverLimit);
            }

            // --- Execute Deletion ---
            if ($imagesToDelete->isNotEmpty()) {
                foreach ($imagesToDelete as $imageToDelete) {
                    if ($isDryRun) {
                        $this->line("\n<fg=yellow>[Dry Run]</> Would delete image file: {$imageToDelete->file_path}");
                    } else {
                        if (Storage::disk('public')->exists($imageToDelete->file_path)) {
                            Storage::disk('public')->delete($imageToDelete->file_path);
                            $totalFilesDeleted++;
                        }
                        // Use forceDelete because the Image model has SoftDeletes
                        $imageToDelete->forceDelete();
                        $totalImagesDeleted++;
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n--- Cleanup Complete ---");
        if ($isDryRun) {
            $this->warn("Dry run finished. The script identified potential images and files for deletion.");
        } else {
            $this->info("Deleted {$totalImagesDeleted} image records from the database.");
            $this->info("Deleted {$totalFilesDeleted} image files from storage.");
        }

        return 0;
    }
}