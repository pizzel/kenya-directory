<?php

namespace App\Console\Commands;

use App\Models\GoogleReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DumpReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dump:reviews {--limit=5000}';

    /**
     * The console command description.
     */
    protected $description = 'Dumps Google Reviews to the log file for keyword analysis.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = $this->option('limit');
        $this->info("Fetching the latest {$limit} reviews...");

        // FIX: Added 'whereHas' to ensure the business actually exists
        // FIX: Added 'with' to pre-load the business data (much faster)
        $reviews = GoogleReview::with('business')
            ->whereHas('business') // <--- This prevents the error
            ->whereNotNull('text')
            ->where('text', '!=', '')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $this->info("Found {$reviews->count()} valid text reviews. Dumping to log...");

        $bar = $this->output->createProgressBar($reviews->count());
        $bar->start();

        Log::info("\n\n========== START OF REVIEW DUMP ==========\n");

        foreach ($reviews as $review) {
            $cleanText = str_replace(["\r", "\n"], " ", $review->text);
            
            // Safe access using optional chaining just in case, though whereHas should catch it
            $businessName = $review->business->name ?? 'Unknown Business';
            
            Log::info("[{$businessName}] \"{$cleanText}\"");
            
            $bar->advance();
        }

        Log::info("\n========== END OF REVIEW DUMP ==========\n\n");
        
        $bar->finish();
        $this->info("\nDone! Check storage/logs/laravel.log");

        return 0;
    }
}