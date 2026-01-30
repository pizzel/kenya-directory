<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReviewAnalyzerService;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Tag;
use Illuminate\Support\Str;

class CheckMissingMappings extends Command
{
    protected $signature = 'analyzer:check-missing';
    protected $description = 'Checks which Database Categories/Tags/Facilities are missing from the hardcoded ReviewAnalyzerService configuration.';

    private ReviewAnalyzerService $analyzer;

    public function __construct(ReviewAnalyzerService $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle()
    {
        $this->info("🔍 Analyzing ReviewAnalyzerService vs Database...");

        // 1. Use Reflection to access the protected 'getMappings' method
        try {
            $reflection = new \ReflectionClass(ReviewAnalyzerService::class);
            $method = $reflection->getMethod('getMappings');
            $method->setAccessible(true); // Allow us to call the protected method
            $hardcodedMappings = $method->invoke($this->analyzer);
        } catch (\Exception $e) {
            $this->error("Could not read mappings: " . $e->getMessage());
            return 1;
        }

        // 2. Compare Categories
        $this->compareAndReport(
            'CATEGORIES',
            Category::pluck('slug')->toArray(),
            array_keys($hardcodedMappings['categories'])
        );

        // 3. Compare Facilities
        $this->compareAndReport(
            'FACILITIES',
            Facility::pluck('slug')->toArray(),
            array_keys($hardcodedMappings['facilities'])
        );

        // 4. Compare Tags
        $this->compareAndReport(
            'TAGS',
            Tag::pluck('slug')->toArray(),
            array_keys($hardcodedMappings['tags'])
        );

        return 0;
    }

    private function compareAndReport(string $type, array $dbSlugs, array $hardcodedSlugs)
    {
        $this->newLine();
        $this->line("----------------------------------------------------------");
        $this->info("📊 ANALYSIS: $type");
        $this->line("----------------------------------------------------------");

        // Find what is in DB but NOT in the Code
        $missing = array_diff($dbSlugs, $hardcodedSlugs);

        if (empty($missing)) {
            $this->info("✅ All $type from the database are present in the code.");
            return;
        }

        $this->error("⚠️  Found " . count($missing) . " $type in Database but MISSING in Service File:");
        
        // Output a copy-paste ready PHP block
        $this->newLine();
        $this->comment("copy the code below and paste it into ReviewAnalyzerService.php:");
        $this->newLine();
        
        foreach ($missing as $slug) {
            $humanName = str_replace('-', ' ', $slug);
            // We guess some synonyms based on the name to be helpful
            $singular = Str::singular($humanName);
            $plural = Str::plural($humanName);
            
            $synonyms = ["'$humanName'"];
            if ($singular !== $humanName) $synonyms[] = "'$singular'";
            if ($plural !== $humanName) $synonyms[] = "'$plural'";
            
            // Special logic for "falls" request
            if (str_contains($slug, 'waterfall')) {
                $synonyms[] = "'falls'";
                $synonyms[] = "'cascades'";
            }

            $list = implode(', ', array_unique($synonyms));

            $this->line("'$slug' => [");
            $this->line("    $list, // TODO: Add more synonyms here");
            $this->line("],");
        }
        $this->newLine();
    }
}