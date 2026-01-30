<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Services\ReviewAnalyzerService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DebugBusinessTagsCommand extends Command
{
    protected $signature = 'debug:business-tags {name}';
    protected $description = 'Shows exactly which words in a business profile triggered specific category tags.';

    public function handle(): int
    {
        $name = $this->argument('name');
        $business = Business::where('name', 'LIKE', "%{$name}%")->first();

        if (!$business) {
            $this->error("Business not found.");
            return 1;
        }

        $this->info("🕵️ Forensic Analysis for: [{$business->name}]");
        $this->line("--------------------------------------------------");

        // 1. Show existing categories
        $this->info("Current Categories in DB:");
        foreach ($business->categories as $cat) {
            $this->line(" - {$cat->name} (Slug: {$cat->slug})");
        }

        // 2. Prepare the text being analyzed
        $reviewsText = $business->googleReviews->pluck('text')->filter()->join(' ');
        $descText = strip_tags($business->about_us . ' ' . $business->description);
        $fullText = strtolower($business->name . ' ' . $reviewsText . ' ' . $descText);

        // 3. Search using the SAME logic as the Service (Regex + Mappings)
        $this->line("");
        $this->info("Analyzing for False Positives...");
        
        // We simulate a check for 'Wedding Venue' and 'Hospital'
        $testMappings = [
            'wedding-venue' => ['wedding venue', 'wedding reception', 'marriage ceremony', 'reception', 'vows'],
            'hospital'      => ['hospital', 'medical center'],
        ];

        foreach ($testMappings as $slug => $keywords) {
            foreach ($keywords as $k) {
                // THE ACTUAL REGEX LOGIC
                $pattern = '/\b' . preg_quote($k, '/') . '\b/i';
                
                if (preg_match($pattern, $fullText, $matches, PREG_OFFSET_CAPTURE)) {
                    $offset = $matches[0][1];
                    $context = substr($fullText, max(0, $offset - 30), 60);
                    $this->line(" ⚠️ Potential trigger for '{$slug}': Found '{$k}' in context: \"...{$context}...\"");
                }
            }
        }

        return 0;
    }
}