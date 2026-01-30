<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventCategory; // Your EventCategory model
use Illuminate\Support\Str;    // For Str::slug

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This will add new event categories if they don't exist based on their slug,
     * and will NOT delete any existing event categories.
     */
    public function run(): void
    {
        $eventTypes = [
            ['name' => 'Music & Concerts', 'icon_class' => 'fas fa-music'],
            ['name' => 'Festivals', 'icon_class' => 'fas fa-glass-cheers'],
            ['name' => 'Adventure & Outdoors', 'icon_class' => 'fas fa-hiking'],
            ['name' => 'Arts & Culture', 'icon_class' => 'fas fa-palette'],
            ['name' => 'Food & Drink Experiences', 'icon_class' => 'fas fa-utensils'],
            ['name' => 'Sports & Fitness', 'icon_class' => 'fas fa-futbol'],
            ['name' => 'Movies & Cinema', 'icon_class' => 'fas fa-film'],
            ['name' => 'Family & Kids Activities', 'icon_class' => 'fas fa-child'],
            ['name' => 'Nightlife & Parties', 'icon_class' => 'fas fa-moon'], // Or fas fa-cocktail
            ['name' => 'Educational & Workshops', 'icon_class' => 'fas fa-chalkboard-teacher'],
            ['name' => 'Wellness & Retreats', 'icon_class' => 'fas fa-spa'],
            ['name' => 'Markets & Fairs', 'icon_class' => 'fas fa-store'],
            ['name' => 'Charity & Community Events', 'icon_class' => 'fas fa-hands-helping'],
            ['name' => 'Automotive Shows & Meetups', 'icon_class' => 'fas fa-car'],
            ['name' => 'Comedy Nights', 'icon_class' => 'fas fa-laugh-beam'],
            ['name' => 'Gaming & eSports', 'icon_class' => 'fas fa-gamepad'],
            ['name' => 'Fashion Shows & Pop-ups', 'icon_class' => 'fas fa-tshirt'],
            ['name' => 'Holiday & Seasonal Events', 'icon_class' => 'fas fa-calendar-star'],
            ['name' => 'Boating & Water Sports', 'icon_class' => 'fas fa-ship'],
            ['name' => 'Wildlife & Conservation Events', 'icon_class' => 'fas fa-paw'],
            // You can add more specific ones or sub-categories later if needed
        ];

        foreach ($eventTypes as $eventTypeData) {
            EventCategory::firstOrCreate(
                ['slug' => Str::slug($eventTypeData['name'])], // Find/Create based on slug
                [
                    'name' => $eventTypeData['name'],
                    'icon_class' => $eventTypeData['icon_class'] ?? null,
                    // 'parent_id' => null, // Assuming these are all top-level for now
                ]
            );
        }

        $this->command->info('Event Categories seeded successfully!');
    }
}