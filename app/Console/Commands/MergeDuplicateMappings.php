<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MergeDuplicateMappings extends Command
{
    protected $signature = 'mappings:clean';
    protected $description = 'Parses ReviewAnalyzerService.php, merges duplicate array keys, and outputs a clean file.';

    public function handle()
    {
        // 1. Path to your specific file
        $filePath = app_path('Services/ReviewAnalyzerService.php');

        if (!File::exists($filePath)) {
            $this->error("File not found at: $filePath");
            return;
        }

        $content = File::get($filePath);

        $this->info("Parsing file...");

        // 2. We define the 3 main sections we want to process
        $sections = ['categories', 'facilities', 'tags'];
        
        $finalStructure = [];

        foreach ($sections as $section) {
            $this->info("Processing section: $section");
            
            // Extract the text block for this section (e.g., 'categories' => [ ... ])
            // We look for the key followed by => [ and capture until the next section or end of array
            $pattern = "/'$section'\s*=>\s*\[(.*?)\]\s*(?:,|$)/s";
            
            // Note: This matches the outermost bracket of the section. 
            // Since your file has nested brackets, we need to be careful. 
            // A safer manual extraction strategy for this specific file format:
            
            $startString = "'$section' => [";
            $startPos = strpos($content, $startString);
            
            if ($startPos === false) {
                $this->warn("Section '$section' not found.");
                continue;
            }

            // Move pointer past the start string
            $offset = $startPos + strlen($startString);
            $bracketCount = 1;
            $length = strlen($content);
            $sectionBody = '';

            // Walk through characters to find the matching closing bracket
            for ($i = $offset; $i < $length; $i++) {
                $char = $content[$i];
                if ($char === '[') $bracketCount++;
                if ($char === ']') $bracketCount--;
                
                if ($bracketCount === 0) {
                    break;
                }
                $sectionBody .= $char;
            }

            // 3. Now we have the body of the section. Let's find all items.
            // Pattern: 'slug' => [ ... ]
            preg_match_all("/'([\w-]+)'\s*=>\s*\[(.*?)\]/s", $sectionBody, $matches);

            if (empty($matches[1])) {
                $this->warn("No items found in $section");
                continue;
            }

            $mergedItems = [];

            foreach ($matches[1] as $index => $slug) {
                $slug = strtolower($slug); // Normalize slug
                $rawKeywords = $matches[2][$index];

                // Clean up the keywords string
                $rawKeywords = str_replace(["'", '"', "\n", "\r", "\t"], '', $rawKeywords);
                $keywords = explode(',', $rawKeywords);

                // Trim and remove empty
                $keywords = array_filter(array_map('trim', $keywords));

                // Initialize if not exists
                if (!isset($mergedItems[$slug])) {
                    $mergedItems[$slug] = [];
                }

                // Merge (this is where we combine duplicates)
                $mergedItems[$slug] = array_merge($mergedItems[$slug], $keywords);
            }

            // Unique and Sort the specific keywords
            foreach ($mergedItems as $slug => $kws) {
                $unique = array_unique($kws);
                sort($unique); // Sort keywords alphabetically
                $mergedItems[$slug] = $unique;
            }

            // Sort the Slugs alphabetically
            ksort($mergedItems);

            $finalStructure[$section] = $mergedItems;
        }

        // 4. Generate the Output
        $output = $this->generatePhpArray($finalStructure);
        
        // 5. Write to a new file so we don't destroy the original immediately
        $outputPath = base_path('CleanedMappings.php');
        File::put($outputPath, $output);

        $this->info("------------------------------------------------");
        $this->info("SUCCESS! Merged mappings written to:");
        $this->comment($outputPath);
        $this->info("------------------------------------------------");
        $this->info("You can now copy the array from that file back into ReviewAnalyzerService.php");
    }

    private function generatePhpArray($data)
    {
        $out = "<?php\n\nreturn [\n";

        foreach ($data as $section => $items) {
            $out .= "    '$section' => [\n";
            
            foreach ($items as $slug => $keywords) {
                $out .= "        '$slug' => [\n";
                // Wrap keywords in quotes
                $formattedKeywords = array_map(fn($k) => "            '$k'", $keywords);
                $out .= implode(",\n", $formattedKeywords);
                $out .= "\n        ],\n";
            }
            
            $out .= "    ],\n";
        }

        $out .= "];\n";
        return $out;
    }
}