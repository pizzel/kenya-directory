<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReviewAnalyzerService;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Tag;
use ReflectionClass;

class VerifyAnalyzerMappings extends Command
{
    protected $signature = 'analyzer:verify';
    protected $description = 'Verifies that mappings in ReviewAnalyzerService match database slugs.';

    public function handle()
    {
        $this->info("🔍 Starting Analysis of ReviewAnalyzerService Mappings vs Database...");

        // 1. Get the Mappings from the Service (using Reflection since it's protected)
        $service = new ReviewAnalyzerService();
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getMappings');
        $method->setAccessible(true); // Allow us to call the protected method
        $mappings = $method->invoke($service);

        // 2. Fetch all actual slugs from Database
        $this->comment("Fetching database records...");
        $dbData = [
            'categories' => Category::pluck('slug')->toArray(),
            'facilities' => Facility::pluck('slug')->toArray(),
            'tags'       => Tag::pluck('slug')->toArray(),
        ];

        // 3. Compare each section
        $this->analyzeSection('Categories', $mappings['categories'], $dbData['categories']);
        $this->analyzeSection('Facilities', $mappings['facilities'], $dbData['facilities']);
        $this->analyzeSection('Tags',       $mappings['tags'],       $dbData['tags']);

        $this->info("✅ Verification Complete.");
    }

    private function analyzeSection(string $label, array $codeMappings, array $dbSlugs)
    {
        $this->newLine();
        $this->info("------------------------------------------------");
        $this->info(" 📊 ANALYZING: " . strtoupper($label));
        $this->info("------------------------------------------------");

        $codeKeys = array_keys($codeMappings);

        // 1. CRITICAL ERRORS: Defined in Code, but MISSING in Database
        // This causes the logic to run, find matches, but fail to save them (Logic ID = null)
        $missingInDb = array_diff($codeKeys, $dbSlugs);

        if (count($missingInDb) > 0) {
            $this->error("🚨 CRITICAL MISMATCHES (Code defined, DB missing):");
            $this->warn("These keywords will be matched but CANNOT be saved because the slug doesn't exist in the DB.");
            
            $rows = [];
            foreach ($missingInDb as $slug) {
                // Try to find a similar slug in DB to offer a suggestion
                $suggestion = $this->findClosestMatch($slug, $dbSlugs);
                $rows[] = [$slug, $suggestion ? "Did you mean: '$suggestion'?" : 'No match found'];
            }
            $this->table(['Slug in Code (ReviewAnalyzerService)', 'Suggestion (In DB)'], $rows);
        } else {
            $this->info("✅ All code mappings exist in the database.");
        }

        // 2. WARNINGS: Defined in Database, but MISSING in Code
        // This means you have categories in your DB that the analyzer will NEVER find automatically.
        $missingInCode = array_diff($dbSlugs, $codeKeys);

        if (count($missingInCode) > 0) {
            $this->newLine();
            $this->comment("⚠️  ORPHANED DB RECORDS (DB exists, Code missing):");
            $this->line("The analyzer has no keywords for these. They will never be auto-assigned.");
            
            // Chunking output if there are too many
            $chunks = array_chunk($missingInCode, 5);
            foreach ($chunks as $chunk) {
                $this->line("   - " . implode(', ', $chunk));
            }
        }
    }

    /**
     * Simple Levenshtein distance check to find typos
     */
    private function findClosestMatch($input, $options)
    {
        $shortest = -1;
        $closest = null;

        foreach ($options as $option) {
            $lev = levenshtein($input, $option);

            if ($lev == 0) return $option;

            if ($lev <= 4 && ($shortest < 0 || $lev < $shortest)) {
                $closest  = $option;
                $shortest = $lev;
            }
        }

        return $closest;
    }
}