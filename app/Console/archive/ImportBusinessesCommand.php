<?php

namespace App\Console\Commands;

use App\Services\GoogleImportService;
use Illuminate\Console\Command;

class ImportBusinessesCommand extends Command
{
    /**
     * The signature now allows for a query, a county, and a primary activity.
     * Example: php artisan import:businesses --county="Nairobi" --activity="Go-Karting" "Top places with go karts"
     */
    protected $signature = 'import:businesses {query} {--county=} {--activity=}';

    protected $description = 'Imports business listings from Google Maps with a primary activity';

    public function handle(GoogleImportService $importer)
    {
        $query = $this->argument('query');
        $county = $this->option('county');
        $activity = $this->option('activity'); // This is the new primary activity name

        $logMessage = "Starting import for query: '{$query}'";
        if ($county) $logMessage .= " in county: {$county}";
        if ($activity) $logMessage .= " with primary activity: '{$activity}'";
        
        $this->info($logMessage . "...");

        // Pass all three arguments to the service
        $result = $importer->import($query, $county, $activity);

        if ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }

        return 0;
    }
}