<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\County;
use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateCollectionsCommand extends Command
{
    protected $signature = 'collections:generate';
    protected $description = 'Creates and populates discovery collections based on predefined themes.';

    // Define your collection themes here
    private array $collectionThemes = [
        [
            'title' => 'Must-Do Experiences in Kenya',
            'criteria' => ['limit' => 20, 'orderBy' => 'views_count'],
        ],
        [
            'title' => 'Top Places to Visit in Kenya',
            'criteria' => ['limit' => 25, 'orderBy' => 'average_rating'],
        ],
        [
            'title' => 'Top Safari Adventures in Kenya',
            'criteria' => ['limit' => 15, 'activity' => 'Wildlife Safari'],
        ],
        [
            'title' => 'Top 10 Fun Activities in Nairobi',
            'criteria' => ['limit' => 10, 'county' => 'Nairobi City', 'orderBy' => 'views_count'],
        ],
        [
            'title' => 'Top 10 Things to Do in Mombasa',
            'criteria' => ['limit' => 10, 'county' => 'Mombasa', 'orderBy' => 'average_rating'],
        ],
        [
            'title' => 'Top Adventure Activities in Nakuru',
            'criteria' => ['limit' => 10, 'county' => 'Nakuru', 'activity' => ['Ziplining', 'Hiking & Trekking', 'Rock Climbing (Outdoor)']],
        ],
        [
            'title' => 'Top Outdoor Activities in Naivasha',
            'criteria' => ['limit' => 10, 'county' => 'Nakuru', 'activity' => ['Lake Boating', 'Bike Riding', 'Camping']],
        ],
    ];

   public function handle(): int
{
    $this->info("Starting to generate/update Discovery Collections...");

            foreach ($this->collectionThemes as $theme) {
                $this->line("Processing: {$theme['title']}");

                $collection = DiscoveryCollection::firstOrCreate(
                    ['slug' => Str::slug($theme['title'])],
                    ['title' => $theme['title']]
                );

                $query = Business::where('status', 'active');
                $criteria = $theme['criteria'];

                if (isset($criteria['county'])) {
                    $query->whereHas('county', fn($q) => $q->where('name', 'like', "%{$criteria['county']}%"));
                }
                if (isset($criteria['activity'])) {
                    $activities = (array) $criteria['activity'];
                    $query->whereHas('categories', fn($q) => $q->whereIn('name', $activities));
                }
                
                // <<< THE OPTIMIZED LOGIC >>>
                // We now add a condition to only find businesses that HAVE media.
                $query->has('media');

                $query->orderBy($criteria['orderBy'] ?? 'views_count', 'desc');
                $businesses = $query->take($criteria['limit'])->get();

                if ($businesses->isEmpty()) {
                    $this->warn(" -> No businesses found matching criteria. Skipping.");
                    continue;
                }
                
                $collection->businesses()->sync($businesses->pluck('id'));

                // Since we know the first business has media, this is now safe and fast.
                $collection->cover_image_url = $businesses->first()->getFirstMedia('images')?->getUrl('card');
                $collection->save();
                // <<< END OF OPTIMIZED LOGIC >>>

                $this->info(" -> Synced {$businesses->count()} businesses and set cover image.");
            }

            $this->info("\nDiscovery Collection generation complete!");
            return 0;
        }
}