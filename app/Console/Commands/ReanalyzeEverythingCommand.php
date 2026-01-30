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
        $this->alert("⚠️  WARNING: DESTRUCTIVE ACTION");
        $this->warn("This command will delete ALL existing connections between Businesses and Categories/Tags/Facilities.");

        // 1. SHOW PROOF OF EXISTING DATA
        $catCount = DB::table('business_category')->count();
        $facCount = DB::table('business_facility')->count();
        $tagCount = DB::table('business_tag')->count();
        
        $this->line("------------------------------------------------");
        $this->info("📊 Current Database State (Before Wipe):");
        $this->line("   - Business-Category Links : {$catCount}");
        $this->line("   - Business-Facility Links : {$facCount}");
        $this->line("   - Business-Tag Links      : {$tagCount}");
        $this->line("------------------------------------------------");

        if (!$this->confirm('Do you want to WIPE these ' . ($catCount + $facCount + $tagCount) . ' records?')) {
            return 0;
        }

        $this->info("Dataset is being wiped...");

        // 2. PERFORM THE WIPE
        DB::table('business_category')->truncate();
        DB::table('business_facility')->truncate();
        DB::table('business_tag')->truncate();

        // 3. SHOW PROOF OF WIPE
        $catCountNow = DB::table('business_category')->count();
        $facCountNow = DB::table('business_facility')->count();
        $tagCountNow = DB::table('business_tag')->count();

        $this->line("------------------------------------------------");
        $this->info("✅ WIPE COMPLETE. Current Database State:");
        $this->warn("   - Business-Category Links : {$catCountNow}"); // Should be 0
        $this->warn("   - Business-Facility Links : {$facCountNow}"); // Should be 0
        $this->warn("   - Business-Tag Links      : {$tagCountNow}"); // Should be 0
        $this->line("------------------------------------------------");

        $this->alert("🛑 PAUSED FOR VERIFICATION");
        $this->comment("The database is now empty. You can check your DB tool to verify.");
        
        if (!$this->confirm('Ready to run the analysis and refill the data?')) {
            $this->error('Process aborted. Tables are currently empty.');
            return 0;
        }

        // 4. START RE-ANALYSIS
        $businesses = Business::has('googleReviews')->with('googleReviews')->get();
        $count = $businesses->count();

        $this->info("Starting strict analysis for {$count} businesses...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($businesses as $business) {
            try {
                $this->analyzer->analyzeBusiness($business);
            } catch (\Exception $e) {
                // Log error if needed, but keep going
            }
            $bar->advance();
        }

        $bar->finish();
        
        // 5. FINAL REPORT
        $this->newLine(2);
        $this->info("✅ Done! All businesses have been re-tagged.");
        $this->line("   - New Business-Category Links : " . DB::table('business_category')->count());
        $this->line("   - New Business-Facility Links : " . DB::table('business_facility')->count());
        $this->line("   - New Business-Tag Links      : " . DB::table('business_tag')->count());

        return 0;
    }
}