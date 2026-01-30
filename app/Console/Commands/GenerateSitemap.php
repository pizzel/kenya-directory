<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// <<< ADD THESE TWO LINES >>>
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
// Your other existing 'use' statements for your models
use App\Models\Business;
use App\Models\Category;
use App\Models\County;
use App\Models\DiscoveryCollection;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Add static pages
        $sitemap->add(Url::create(route('home'))->setPriority(1.0));
        $sitemap->add(Url::create(route('listings.index'))->setPriority(0.9));
        $sitemap->add(Url::create(route('collections.index'))->setPriority(0.9));
        $sitemap->add(Url::create(route('contact.show'))->setPriority(0.7));

        // Add all active businesses
        Business::where('status', 'active')->get()->each(function (Business $business) use ($sitemap) {
            $sitemap->add(Url::create(route('listings.show', $business->slug))
                ->setLastModificationDate($business->updated_at)
                ->setPriority(0.8));
        });

        // Add all categories, counties, collections etc.
        Category::all()->each(fn(Category $c) => $sitemap->add(route('listings.category', $c->slug)));
        County::all()->each(fn(County $c) => $sitemap->add(route('listings.county', $c->slug)));
        DiscoveryCollection::where('is_active', true)->get()->each(fn(DiscoveryCollection $c) => $sitemap->add(route('collections.show', $c->slug)));

        $sitemap->writeToFile('/home/discove6/public_html/sitemap.xml');

        $this->info('Sitemap generated successfully!');
    }
}