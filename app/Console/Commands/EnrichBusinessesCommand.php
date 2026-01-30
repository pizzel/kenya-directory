<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\GoogleImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class EnrichBusinessesCommand extends Command
{
    /**
     * The signature allows for three modes:
     * 1. No arguments: Enriches missing businesses (Default).
     * 2. --force: Enriches ALL businesses (Weekly Refresh).
     * 3. --name="XYZ": Enriches specific businesses matching the name (Targeted).
     */
    protected $signature = 'businesses:enrich 
                            {--force : Force update all businesses} 
                            {--name= : Enrich specific businesses by name}';

    protected $description = 'Enriches business details, ratings, and reviews from Google. Can target missing, all, or specific businesses.';

    private GoogleImportService $importer;

    public function __construct(GoogleImportService $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    public function handle(): int
    {
        $name = $this->option('name');
        $force = $this->option('force');

        // --- MODE 1: TARGETED BY NAME ---
        if ($name) {
            $this->info("Mode: TARGETED. Searching for businesses matching: '{$name}'...");
            
            $query = Business::where('name', 'like', "%{$name}%")->with('county')->orderBy('id');
            $count = $query->count();

            if ($count === 0) {
                $this->error("No businesses found matching: '{$name}'");
                return 1;
            }

            $this->info("Found {$count} matching business(es).");
        } 
        // --- MODE 2: FORCE ALL ---
        elseif ($force) {
            $this->info("Mode: FORCE (Weekly Refresh). Processing ALL businesses.");
            $query = Business::with('county')->orderBy('id');
        } 
        // --- MODE 3: MISSING ONLY (DEFAULT) ---
        else {
            $this->info("Mode: MISSING. Processing businesses with empty descriptions.");
            $query = Business::where(function ($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', 'LIKE', '%trimmed%');
            })->with('county')->orderBy('id');
        }

        $count = $query->count(); // Recount for other modes if needed, though redundant for name mode it's fast.

        if ($count === 0 && !$name) {
            $this->info("No businesses found to process.");
            return 0;
        }

        $this->info("Starting enrichment for {$count} businesses...");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $enrichedCount = 0;
        $skippedCount = 0;

        // Process in chunks to save memory
        $query->chunk(100, function ($businesses) use ($progressBar, &$enrichedCount, &$skippedCount) {
            foreach ($businesses as $business) {
                try {
                    // We use the importer service. 
                    // true = skipImages (we strictly want text data here usually, unless configured otherwise in service)
                    // You can toggle this boolean if you want images for the targeted name search.
                    $result = $this->importer->importSingleByName($business, true);
                    
                    if ($result['success']) {
                        $enrichedCount++;
                    } else {
                        $skippedCount++;
                        // Optional: Log failures to file or output verbose if requested
                    }

                } catch (\Exception $e) {
                    Log::error("Enrich Command Error processing {$business->name}: " . $e->getMessage());
                    $skippedCount++;
                }

                $progressBar->advance();
                
                // Rate Limiting: Sleep 1 second to respect Google API
                sleep(1); 
            }
        });

        $progressBar->finish();

        $this->info("\n\n-----------------------------------------");
        $this->info("Enrichment Process Complete.");
        $this->info("Successfully Refreshed: {$enrichedCount}");
        $this->info("Skipped / API Failures: {$skippedCount}");
        $this->info("-----------------------------------------");

        return 0;
    }
}