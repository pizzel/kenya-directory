<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\GoogleImportService;
use Illuminate\Console\Command;

class EnrichByNameCommand extends Command
{
    /**
     * The signature takes a required business name.
     * Example: php artisan business:enrich-by-name "Pioneer Hotel"
     */
    protected $signature = 'business:enrich-by-name {name}';

    /**
     * The console command description.
     */
    protected $description = 'Finds one or more businesses in the local database by name and enriches them with data from Google.';

    private GoogleImportService $importer;

    public function __construct(GoogleImportService $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $businessName = $this->argument('name');

        $this->info("Searching for businesses in your database matching the name: '{$businessName}'...");

        // Find all businesses in our local database that match the name.
        // We use 'with('county')' to have the county name ready.
        $businessesToEnrich = Business::where('name', 'like', "%{$businessName}%")
                                        ->with('county')
                                        ->get();

        if ($businessesToEnrich->isEmpty()) {
            $this->error("No businesses found in your database with the name: '{$businessName}'");
            return 1;
        }

        $this->info("Found {$businessesToEnrich->count()} matching business(es). Starting enrichment process...");
        $progressBar = $this->output->createProgressBar($businessesToEnrich->count());
        $enrichedCount = 0;
        $failedCount = 0;

        $progressBar->start();

        foreach ($businessesToEnrich as $business) {
            $this->line("\nProcessing '{$business->name}' (ID: {$business->id}) in {$business->county->name}...");
            
            // We use the powerful `importSingleByName` method to re-find its specific counterpart on Google.
            $result = $this->importer->importSingleByName(
                $business->name,
                $business->county->name ?? null
            );
            
            if ($result['success']) {
                $this->info(" -> Success!");
                $enrichedCount++;
            } else {
                $this->warn(" -> Failed: " . $result['message']);
                $failedCount++;
            }
            
            $progressBar->advance();
            sleep(1); // Be respectful to the API.
        }

        $progressBar->finish();

        $this->info("\n\nEnrichment process complete.");
        $this->info("Successfully enriched {$enrichedCount} business(es).");
        if ($failedCount > 0) {
            $this->warn("Failed to enrich {$failedCount} business(es). Check logs for details.");
        }

        return 0;
    }
}