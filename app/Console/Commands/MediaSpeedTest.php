<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use Spatie\MediaLibrary\Conversions\FileManipulator;

class MediaSpeedTest extends Command
{
    protected $signature = 'media:speed-test';
    protected $description = 'Tests the conversion speed of your media library';

    public function handle()
    {
        $business = Business::whereHas('media')->first();
        
        if (!$business) {
            $this->error("No business with media found to test.");
            return 1;
        }

        $media = $business->getFirstMedia('images');
        $this->info("--- Media Speed Test ---");
        $this->line("Business: " . $business->name);
        $this->line("Media ID: " . $media->id);
        $this->line("File Name: " . $media->file_name);
        $this->line("------------------------");

        // Test Card JPG
        $this->comment("Converting to 'card' (JPG)...");
        $start = microtime(true);
        app(FileManipulator::class)->createDerivedFiles($media, ['card']);
        $end = microtime(true);
        $this->info("✔ JPG Card Speed: " . round($end - $start, 4) . " seconds");

        // Test Hero WebP
        $this->comment("Converting to 'hero' (WebP)...");
        $start = microtime(true);
        app(FileManipulator::class)->createDerivedFiles($media, ['hero']);
        $end = microtime(true);
        $this->info("✔ WebP Hero Speed: " . round($end - $start, 4) . " seconds");

        $this->line("------------------------");
        $this->info("Total time: " . round(microtime(true) - $start, 4) . " seconds");
        
        return 0;
    }
}
