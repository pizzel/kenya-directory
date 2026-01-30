<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use Symfony\Component\Finder\Finder;
// <<< IMPORT THE NEW V3 ENCODERS >>>
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Exceptions\NotReadableException;

class OptimizeExistingImagesCommand extends Command
{
    /**
     * The signature of the console command.
     */
    protected $signature = 'images:optimize-existing {--quality=85}';

    /**
     * The console command description.
     */
    protected $description = 'Optimizes all existing JPG and PNG images in the public storage directory using Intervention Image v3.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $quality = (int) $this->option('quality');
        if ($quality < 0 || $quality > 100) {
            $this->error('Quality must be between 0 and 100.');
            return 1;
        }

        $this->info("Starting image optimization process with JPEG quality: {$quality}%...");

        $path = Storage::disk('public')->path('/');
        $files = (new Finder())->in($path)->files()->name('/\.(jpg|jpeg|png)$/i');

        if (!iterator_count($files)) {
            $this->info('No images found to optimize.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar(iterator_count($files));
        $optimizedCount = 0;
        $totalSavings = 0;

        $progressBar->start();

        foreach ($files as $file) {
            try {
                $originalSize = $file->getSize();
                $filePath = $file->getRealPath();
                $extension = strtolower($file->getExtension());
                
                // Read the image data. Use a try-catch for corrupted files.
                $image = InterventionImage::read($filePath);
                
                $optimizedImageStream = null;

                // Use the correct encoder based on the file type
                if ($extension === 'png') {
                    // PNG quality is 0-9. Let's use a medium-high setting.
                    $optimizedImageStream = (string) $image->encode(new PngEncoder(level: 7));
                } else { // Handles jpg and jpeg
                    $optimizedImageStream = (string) $image->encode(new JpegEncoder(quality: $quality));
                }

                $newSize = strlen($optimizedImageStream);

                // ONLY overwrite the original file IF the new version is smaller and valid.
                if ($newSize > 0 && $newSize < $originalSize) {
                    $relativePath = substr($filePath, strlen(storage_path('app/public/')) + 1);
                    Storage::disk('public')->put($relativePath, $optimizedImageStream);
                    
                    $optimizedCount++;
                    $totalSavings += ($originalSize - $newSize);
                }
            }
            catch (NotReadableException $e) {
                $this->error("\nCould not read file (it may be corrupted): {$file->getFilename()}");
            }
            catch (\Exception $e) {
                $this->error("\nFailed to process file: {$file->getFilename()}. Error: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();

        $totalSavingsMb = round($totalSavings / 1024 / 1024, 2);
        $this->info("\n\nOptimization complete!");
        $this->info("Successfully optimized {$optimizedCount} images (others were already optimized or skipped).");
        $this->info("Total file size saved: {$totalSavingsMb} MB.");

        return 0;
    }
}