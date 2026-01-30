<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateIntroTextCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'businesses:generate-intro {--force} {--limit=250}';

    /**
     * The console command description.
     */
    protected $description = 'Generates a short "About Us" intro from the full description for businesses where it is missing.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to generate "About Us" intro text...');

        $characterLimit = (int) $this->option('limit');

        // Find all businesses that have a description but the about_us field is empty or null.
        $query = Business::whereNotNull('description')
                         ->where(function ($q) {
                             $q->whereNull('about_us')->orWhere('about_us', '');
                         });

        // The --force flag will regenerate the intro for ALL businesses that have a description.
        if ($this->option('force')) {
            $this->warn("Using --force flag. 'About Us' will be regenerated for ALL businesses with a description.");
            $query = Business::whereNotNull('description');
        }

        $businessesToUpdate = $query->get();

        if ($businessesToUpdate->isEmpty()) {
            $this->info('No businesses found that need an "About Us" intro generated. All set!');
            return 0;
        }

        $this->info("Found {$businessesToUpdate->count()} businesses to update.");
        $progressBar = $this->output->createProgressBar($businessesToUpdate->count());

        $progressBar->start();

        foreach ($businessesToUpdate as $business) {
            // Generate the truncated intro text
            $introText = Str::limit($business->description, $characterLimit, '...');

            // Update the business record without touching updated_at timestamps
            $business->about_us = $introText;
            $business->saveQuietly();

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\nSuccessfully generated 'About Us' intro text for {$businessesToUpdate->count()} businesses.");

        return 0;
    }
}