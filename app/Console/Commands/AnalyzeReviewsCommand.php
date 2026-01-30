<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\ReviewAnalyzerService;
use Illuminate\Console\Command;

class AnalyzeReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'businesses:analyze-reviews {--limit=0}';

    /**
     * The console command description.
     */
    protected $description = 'Scans Google Reviews to populate Categories, Facilities, and Tags based on keywords.';

    private ReviewAnalyzerService $analyzer;

    public function __construct(ReviewAnalyzerService $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle(): int
    {
        $this->info("Starting Semantic Review Analysis...");

        // Only fetch businesses that actually have reviews
        $query = Business::has('googleReviews');

        if ($this->option('limit') > 0) {
            $query->limit($this->option('limit'));
        }

        $count = $query->count();
        $this->info("Found {$count} businesses with reviews to analyze.");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Process in chunks of 100 to keep memory low
        $query->chunk(100, function ($businesses) use ($bar) {
            foreach ($businesses as $business) {
                try {
                    $this->analyzer->analyzeBusiness($business);
                } catch (\Exception $e) {
                    // Fail silently for one business, continue to next
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n\nAnalysis Complete! Database populated with new tags.");

        return 0;
    }
}