<?php

namespace App\Console\Commands;

use App\Services\GoogleImportService;
use Illuminate\Console\Command;

class ImportSingleBusinessCommand extends Command
{
    /**
     * The signature takes a required name and optional flags.
     * Example: php artisan import:single-business "The Carnivore Restaurant" --county="Nairobi"
     */
    protected $signature = 'import:single-business {name} {--county=} {--activity=}';

    protected $description = 'Imports a single, specific business by its name using the Google Places API.';

    public function handle(GoogleImportService $importer)
    {
        $name = $this->argument('name');
        $county = $this->option('county');
        $activity = $this->option('activity');

        $logMessage = "Attempting to import single business: '{$name}'";
        if ($county) $logMessage .= " in county: {$county}";
        if ($activity) $logMessage .= " with primary activity: '{$activity}'";

        $this->info($logMessage . "...");

        // Call the NEW method on the service
        $result = $importer->importSingleByName($name, $county, $activity);

        if ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }

        return 0;
    }
}