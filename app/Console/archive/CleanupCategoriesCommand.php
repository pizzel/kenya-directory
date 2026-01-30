<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class CleanupCategoriesCommand extends Command
{
    protected $signature = 'categories:cleanup';
    protected $description = 'Deletes categories that have 0 businesses attached.';

    public function handle(): int
    {
        $this->info("Scanning for unused categories...");

        // Find categories with 0 businesses
        // We utilize the 'businesses' relationship count
        $unused = Category::doesntHave('businesses')->get();

        if ($unused->isEmpty()) {
            $this->info("Database is clean! No unused categories found.");
            return 0;
        }

        $this->info("Found {$unused->count()} unused categories.");
        
        // List them first so you can see what will happen
        $headers = ['ID', 'Name', 'Slug'];
        $this->table($headers, $unused->map->only(['id', 'name', 'slug'])->toArray());

        if ($this->confirm("Do you want to delete these {$unused->count()} categories?")) {
            $bar = $this->output->createProgressBar($unused->count());
            $bar->start();

            foreach ($unused as $category) {
                $category->delete();
                $bar->advance();
            }

            $bar->finish();
            $this->info("\n\nCleanup complete. Unused categories removed.");
        } else {
            $this->info("Operation cancelled.");
        }

        return 0;
    }
}