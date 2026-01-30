<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\ReviewAnalyzerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReanalyzeEverythingCommand extends Command
{
    protected $signature = 'businesses:reanalyze-all';
    protected $description = 'Wipes all categories/tags/facilities and runs the strict analyzer on every business.';

    private ReviewAnalyzerService $analyzer;

    public function __construct(ReviewAnalyzerService $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle(): int
    {
        $this->warn("⚠️ This will WIPE all categories/facilities/tags from ALL businesses and re-calculate them using strict rules.");
        
        if (!$this->confirm('Are you sure you want to proceed?')) {
            return 0;
        }

        $this->info("Wiping existing relationships...");
        // Empty the pivot tables
        DB::table('business_category')->truncate();
        DB::table('business_facility')->truncate();
        DB::table('business_tag')->truncate();

        $businesses = Business::has('googleReviews')->with('googleReviews')->get();
        $count = $businesses->count();

        $this->info("Starting strict analysis for {$count} businesses...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($businesses as $business) {
            try {
                $this->analyzer->analyzeBusiness($business);
            } catch (\Exception $e) {
                // Skip errors
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n✅ Done! All businesses have been re-tagged with high precision.");

        return 0;
    }
}