<?php

namespace App\Console\Commands;

use App\Services\GoogleImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnrichSingleDebugCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'businesses:enrich-one {name}';

    /**
     * The console command description.
     */
    protected $description = 'Debug enrich a single business by name and log the Google response.';

    private GoogleImportService $importer;

    public function __construct(GoogleImportService $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $this->info("Starting debug enrichment for: '{$name}'");

        // Try to find the business in DB first to pass the object (Strict Mode)
        $business = \App\Models\Business::where('name', $name)->first();

        if ($business) {
            $this->info("Found business in DB (ID: {$business->id}). Using Strict Update Mode.");
            $input = $business;
        } else {
            $this->warn("Business not found in DB. Using Legacy Name Search Mode.");
            $input = $name;
        }

        // Call the importer with the new signature:
        // arg1: Business Object (or name string)
        // arg2: skipImages (boolean) - We set this to TRUE for safety in debug
        $result = $this->importer->importSingleByName($input, true);

        if ($result['success']) {
            $this->info("Success! " . $result['message']);
        } else {
            $this->error("Failed: " . $result['message']);
        }

        return 0;
    }
}