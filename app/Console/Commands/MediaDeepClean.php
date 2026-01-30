<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaDeepClean extends Command
{
    protected $signature = 'media:deep-clean';
    protected $description = 'Wiped all existing thumbnails/conversions from disk to reclaim space and prepare for fresh SEO regeneration.';

    public function handle()
    {
        $this->info("Starting Deep Clean of Media Conversions...");

        $mediaCount = Media::count();
        $bar = $this->output->createProgressBar($mediaCount);
        $bar->start();

        foreach (Media::all() as $media) {
            $conversionsPath = dirname($media->getPath()) . DIRECTORY_SEPARATOR . 'conversions';

            if (File::isDirectory($conversionsPath)) {
                File::deleteDirectory($conversionsPath);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n✔ All old conversions deleted successfully.");
        $this->info("Now run: php artisan media-library:regenerate");
        
        return 0;
    }
}
