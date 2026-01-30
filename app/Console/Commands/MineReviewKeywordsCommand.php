<?php

namespace App\Console\Commands;

use App\Models\GoogleReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MineReviewKeywordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reviews:mine {--min=5}';

    /**
     * The console command description.
     */
    protected $description = 'Analyzes review text to find most frequent 2-word and 3-word phrases.';

    // Common words to ignore
    private $stopWords = [
        'the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it', 'on', 'you', 'this', 'for', 'but', 'with', 'are', 'have', 'be', 'at', 'or', 'as', 'was', 'so', 'if', 'out', 'not', 'very', 'good', 'great', 'nice', 'place', 'really', 'just', 'from', 'all', 'by', 'an', 'we', 'my', 'had', 'were', 'they', 'go', 'time', 'visit', 'one', 'would', 'there', 'their', 'has', 'been', 'will', 'much', 'too', 'can', 'us', 'me', 'up', 'some', 'even', 'when', 'get', 'also', 'about', 'best', 'well', 'service', 'staff', 'food', 'experience', 'environment', 'atmosphere'
    ];

    public function handle(): int
    {
        $minCount = (int) $this->option('min');
        $this->info("Mining reviews for phrases appearing at least {$minCount} times...");

        // 1. Fetch Reviews (Chunking to handle memory)
        // We pluck 'text' to keep it light
        $query = GoogleReview::whereNotNull('text')->where('text', '!=', '');
        $total = $query->count();
        $this->info("Analyzing {$total} reviews...");

        $bigrams = []; // 2 words (e.g. "heated pool")
        $trigrams = []; // 3 words (e.g. "value for money")

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(500, function ($reviews) use (&$bigrams, &$trigrams, $bar) {
            foreach ($reviews as $review) {
                $text = strtolower($review->text);
                // Remove punctuation/symbols, keep only letters and spaces
                $text = preg_replace('/[^a-z\s]/', '', $text);
                
                $words = explode(' ', $text);
                // Filter empty strings and stop words
                $cleanWords = array_values(array_filter($words, function($w) {
                    return !empty($w) && !in_array($w, $this->stopWords) && strlen($w) > 2;
                }));

                $count = count($cleanWords);

                // Generate Bigrams (2 words)
                for ($i = 0; $i < $count - 1; $i++) {
                    $phrase = $cleanWords[$i] . ' ' . $cleanWords[$i+1];
                    if (!isset($bigrams[$phrase])) $bigrams[$phrase] = 0;
                    $bigrams[$phrase]++;
                }

                // Generate Trigrams (3 words)
                for ($i = 0; $i < $count - 2; $i++) {
                    $phrase = $cleanWords[$i] . ' ' . $cleanWords[$i+1] . ' ' . $cleanWords[$i+2];
                    if (!isset($trigrams[$phrase])) $trigrams[$phrase] = 0;
                    $trigrams[$phrase]++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nProcessing and sorting results...");

        // Filter by minimum count and Sort
        $finalBigrams = array_filter($bigrams, fn($c) => $c >= $minCount);
        arsort($finalBigrams);

        $finalTrigrams = array_filter($trigrams, fn($c) => $c >= $minCount);
        arsort($finalTrigrams);

        // Output to CSV
        $filename = 'review_mining_results_' . date('Y-m-d_His') . '.csv';
        $handle = fopen(storage_path('app/' . $filename), 'w');
        
        fputcsv($handle, ['Type', 'Phrase', 'Count']);

        foreach ($finalBigrams as $phrase => $count) {
            fputcsv($handle, ['2-Word', $phrase, $count]);
        }
        foreach ($finalTrigrams as $phrase => $count) {
            fputcsv($handle, ['3-Word', $phrase, $count]);
        }

        fclose($handle);

        $this->info("\n\nSUCCESS! Results saved to: storage/app/{$filename}");
        $this->info("Top 5 Found:");
        $this->table(['Phrase', 'Count'], array_slice(array_map(fn($k, $v) => [$k, $v], array_keys($finalBigrams), $finalBigrams), 0, 5));

        return 0;
    }
}