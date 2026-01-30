<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class ListCategoriesCommand extends Command
{
    protected $signature = 'categories:list';
    protected $description = 'Lists all categories with their IDs and business counts.';

    public function handle(): int
    {
        $categories = Category::withCount('businesses')
            ->orderBy('name')
            ->get();

        $headers = ['ID', 'Name', 'Slug', 'Business Count'];
        $data = $categories->map(fn($c) => [$c->id, $c->name, $c->slug, $c->businesses_count])->toArray();

        $this->table($headers, $data);
        return 0;
    }
}