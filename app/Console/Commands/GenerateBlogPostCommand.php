<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\County;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateBlogPostCommand extends Command
{
    protected $signature = 'blog:generate {category_name} {county_name} {--simulate : Skip API call and use dummy data}';

    protected $description = 'Generates an AI-written "Top 5" listicle blog post (Use --simulate to test without API cost).';

    public function handle(): int
    {
        // 1. INPUTS
        $catInput = $this->argument('category_name');
        $countyInput = $this->argument('county_name');
        $isSimulation = $this->option('simulate');

        $this->info("🔍 Hunting for '{$catInput}' in '{$countyInput}'...");

        // 2. SMART SEARCH (Find the real DB records)
        $category = Category::where('name', 'LIKE', "%{$catInput}%")
            ->orWhere('name', 'LIKE', "%" . Str::singular($catInput) . "%")
            ->orWhere('slug', 'LIKE', "%" . Str::slug(Str::singular($catInput)) . "%")
            ->first();

        $county = County::where('name', 'LIKE', "%{$countyInput}%")
            ->orWhere('slug', 'LIKE', "%" . Str::slug($countyInput) . "%")
            ->first();

        if (!$category) {
            $this->error("❌ Category '{$catInput}' not found.");
            return 1;
        }
        
        if (!$county) {
            $this->error("❌ County '{$countyInput}' not found.");
            return 1;
        }

        // Define the variables needed for the rest of the script
        $catName = $category->name;      // Use the clean DB name (e.g. "Resort")
        $countyName = $county->name;     // Use the clean DB name (e.g. "Nakuru")

        $this->info("✅ Found: [Category: {$catName}] in [County: {$countyName}]");

        // 3. SELECT TOP 5 BUSINESSES
        $businesses = Business::where('status', 'active')
            ->where('county_id', $county->id)
            ->whereHas('categories', fn($q) => $q->where('id', $category->id))
            ->orderBy('google_rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        if ($businesses->count() < 1) {
            $this->warn("No businesses found matching criteria.");
            return 0;
        }

        $this->info("Found {$businesses->count()} places. Generating content...");

        // 4. GET CONTENT (AI or Simulation)
        $articleData = null;

        if ($isSimulation) {
            $this->warn("⚡ SIMULATION MODE: Using dummy text (No API Cost).");
            // FIXED: Passing the correct variables now
            $articleData = $this->getSimulatedResponse($catName, $countyName, $businesses);
        } else {
            if (!env('OPENAI_API_KEY')) {
                 $this->error("No API Key found! Switching to simulation.");
                 $articleData = $this->getSimulatedResponse($catName, $countyName, $businesses);
            } else {
                 $articleData = $this->getRealAiResponse($catName, $countyName, $businesses);
            }
        }

        if (!$articleData) return 1;

        // 5. ASSEMBLE BLOCKS
        $postContentBlocks = [];

        // Intro
        $postContentBlocks[] = [
            'type' => 'text_block',
            'data' => ['text' => $articleData['intro']]
        ];

        // Businesses
        foreach ($businesses as $business) {
            // Find specific review or use generic fallback
            $reviewText = null;
            foreach ($articleData['reviews'] as $aiName => $text) {
                if (str_contains(strtolower($business->name), strtolower($aiName))) {
                    $reviewText = $text;
                    break;
                }
            }
            // Use plural "Resorts" in the fallback text for better flow
            $reviewText = $reviewText ?? "<p>A top-rated choice for " . Str::plural($catName) . " in {$countyName}. Known for its excellent service and great atmosphere.</p>";

            // Block A: Business Card
            $postContentBlocks[] = [
                'type' => 'business_block',
                'data' => ['business_id' => $business->id]
            ];

            // Block B: Text Review
            $postContentBlocks[] = [
                'type' => 'text_block',
                'data' => ['text' => $reviewText]
            ];
        }

        // Conclusion
        $postContentBlocks[] = [
            'type' => 'text_block',
            'data' => ['text' => $articleData['conclusion']]
        ];

        // 6. SAVE POST
        $author = User::where('role', 'admin')->first() ?? User::first();
        
        $post = Post::create([
            'title' => $articleData['title'],
            'slug' => Str::slug($articleData['title']) . '-' . rand(100,999), 
            'user_id' => $author->id,
            'category_id' => $category->id,
            'content' => $postContentBlocks, 
            'excerpt' => Str::limit(strip_tags($articleData['intro']), 150),
            'meta_description' => $articleData['meta_description'],
            'meta_keywords' => ["{$catName}", "{$countyName}", "Travel Kenya"],
            'status' => 'published',
            'published_at' => now(),
            'is_featured' => false,
        ]);

        $this->info("✅ Success! Created Post ID: {$post->id}");
        $this->info("View it at: /blog/{$post->slug}");

        return 0;
    }

    /**
     * Fake Response for Testing
     */
    private function getSimulatedResponse($cat, $county, $businesses)
    {
        $reviews = [];
        foreach($businesses as $b) {
            $reviews[$b->name] = "<p><strong>{$b->name}</strong> is one of the most popular spots in {$county}. Visitors love the ambience and the {$b->google_rating} star service. It is definitely a must-visit location if you are in the area.</p>";
        }

        return [
            'title' => "Top " . $businesses->count() . " Best {$cat} in {$county} (2026 Review)",
            'meta_description' => "Discover the best {$cat} in {$county}. We reviewed the top rated places including " . $businesses->first()->name . ".",
            'intro' => "<p>Are you looking for the best <strong>{$cat}</strong> in {$county}? You have come to the right place.</p><p>We have analyzed thousands of reviews to bring you the ultimate guide to the top-rated spots in the region.</p>",
            'reviews' => $reviews,
            'conclusion' => "<p>That wraps up our list of the best {$cat} in {$county}. Have you visited any of these? Let us know in the comments below!</p>"
        ];
    }

    /**
     * Real API Call (When you have credits)
     */
    private function getRealAiResponse($catName, $countyName, $businesses)
    {
        // ... (Your previous OpenAI code goes here) ...
        // For now, returning null to force simulation if this method isn't fully set up yet.
        return null; 
    }
}