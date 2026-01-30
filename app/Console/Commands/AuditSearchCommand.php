<?php

namespace App\Console\Commands;

use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AuditSearchCommand extends Command
{
    protected $signature = 'search:audit';
    protected $description = 'Compares DB collections against what is actually in the search bar cache.';

    public function handle(): int
    {
        $this->info("🕵️ Starting Search Suggestion Audit...");

        // 1. Get ALL Active Collections in DB
        $dbCollections = DiscoveryCollection::where('is_active', true)->pluck('title')->toArray();
        $dbCount = count($dbCollections);

        // 2. Get what is CURRENTLY in the Cache
        $cachedCollections = Cache::get('all_collections_for_search', []);
        $cachedTitles = collect($cachedCollections)->pluck('title')->toArray();
        $cacheCount = count($cachedTitles);

        $this->line("--------------------------------------------------");
        $this->info("DATABASE TOTAL: {$dbCount} active collections.");
        $this->info("CACHE TOTAL: {$cacheCount} collections loaded in search bar.");
        $this->line("--------------------------------------------------");

        if ($dbCount > $cacheCount) {
            $this->warn("⚠️ MISSING DATA: There are " . ($dbCount - $cacheCount) . " collections in your DB that will NEVER show up in search suggestions because of the 'take(10)' limit.");
        }

        $this->info("List of collections NOT in search suggestions:");
        $diff = array_diff($dbCollections, $cachedTitles);
        foreach ($diff as $title) {
            $this->line(" ❌ [Hidden] {$title}");
        }

        return 0;
    }
}