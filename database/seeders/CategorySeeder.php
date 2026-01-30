<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // --- FOOD & DRINK ---
            ['name' => 'Restaurant', 'icon_class' => 'fas fa-utensils'],
            ['name' => 'Cafe & Bistro', 'icon_class' => 'fas fa-coffee'],
            ['name' => 'Bar & Lounge', 'icon_class' => 'fas fa-glass-martini-alt'],
            ['name' => 'Rooftop Bar', 'icon_class' => 'fas fa-cloud'],
            ['name' => 'Night Club', 'icon_class' => 'fas fa-music'],
            ['name' => 'Bakery', 'icon_class' => 'fas fa-bread-slice'],
            ['name' => 'Nyama Choma Zone', 'icon_class' => 'fas fa-fire'],
            ['name' => 'Fast Food', 'icon_class' => 'fas fa-hamburger'],
            ['name' => 'Wine Tasting', 'icon_class' => 'fas fa-wine-glass-alt'],
            ['name' => 'Tea Tasting', 'icon_class' => 'fas fa-mug-hot'],

            // --- ACCOMMODATION ---
            ['name' => 'Hotel', 'icon_class' => 'fas fa-bed'],
            ['name' => 'Resort', 'icon_class' => 'fas fa-umbrella-beach'],
            ['name' => 'Safari Camp', 'icon_class' => 'fas fa-campground'],
            ['name' => 'Airbnb', 'icon_class' => 'fas fa-key'],
            ['name' => 'Villa', 'icon_class' => 'fas fa-home'],
            ['name' => 'Cottage', 'icon_class' => 'fas fa-warehouse'],
            ['name' => 'Staycation', 'icon_class' => 'fas fa-suitcase'],
            ['name' => 'Glamping', 'icon_class' => 'fas fa-campground'],

            // --- FAMILY & RECREATION ---
            ['name' => 'Kids Park', 'icon_class' => 'fas fa-child'],
            ['name' => 'Amusement Park', 'icon_class' => 'fas fa-ticket-alt'],
            ['name' => 'Bouncing Castle', 'icon_class' => 'fas fa-chess-rook'],
            ['name' => 'Trampoline Park', 'icon_class' => 'fas fa-arrow-up'],
            ['name' => 'Indoor Play Area', 'icon_class' => 'fas fa-shapes'],
            ['name' => 'Picnic Site', 'icon_class' => 'fas fa-shopping-basket'],
            ['name' => 'Bowling Alley', 'icon_class' => 'fas fa-bowling-ball'],
            ['name' => 'Escape Room', 'icon_class' => 'fas fa-key'],
            ['name' => 'Gaming Arcade', 'icon_class' => 'fas fa-gamepad'],
            ['name' => 'Virtual Reality', 'icon_class' => 'fas fa-vr-cardboard'],

            // --- ADVENTURE & SPORTS ---
            ['name' => 'Hiking', 'icon_class' => 'fas fa-hiking'],
            ['name' => 'Swimming', 'icon_class' => 'fas fa-swimmer'],
            ['name' => 'Go-Karting', 'icon_class' => 'fas fa-flag-checkered'],
            ['name' => 'Quad Biking', 'icon_class' => 'fas fa-motorcycle'],
            ['name' => 'Ziplining', 'icon_class' => 'fas fa-random'],
            ['name' => 'Paintball', 'icon_class' => 'fas fa-crosshairs'],
            ['name' => 'Archery', 'icon_class' => 'fas fa-bullseye'],
            ['name' => 'Rock Climbing', 'icon_class' => 'fas fa-mountain'],
            ['name' => 'Horse Riding', 'icon_class' => 'fas fa-horse'],
            ['name' => 'Cycling', 'icon_class' => 'fas fa-biking'],
            ['name' => 'Golfing', 'icon_class' => 'fas fa-golf-ball'],
            ['name' => 'Gym & Fitness', 'icon_class' => 'fas fa-dumbbell'],
            ['name' => 'Basketball', 'icon_class' => 'fas fa-basketball-ball'],
            ['name' => 'Football', 'icon_class' => 'fas fa-futbol'],
            ['name' => 'Tennis', 'icon_class' => 'fas fa-table-tennis'],

            // --- WATER ACTIVITIES ---
            ['name' => 'Boat Riding', 'icon_class' => 'fas fa-ship'],
            ['name' => 'Kayaking', 'icon_class' => 'fas fa-water'],
            ['name' => 'Water Rafting', 'icon_class' => 'fas fa-water'],
            ['name' => 'Dhow Cruise', 'icon_class' => 'fas fa-anchor'],
            ['name' => 'Jet Skiing', 'icon_class' => 'fas fa-water'],
            ['name' => 'Scuba Diving', 'icon_class' => 'fas fa-mask'],
            ['name' => 'Snorkeling', 'icon_class' => 'fas fa-swimmer'],
            ['name' => 'Dolphins', 'icon_class' => 'fas fa-fish'],

            // --- NATURE & WILDLIFE ---
            ['name' => 'Game Drive', 'icon_class' => 'fas fa-binoculars'],
            ['name' => 'Bird Watching', 'icon_class' => 'fas fa-dove'],
            ['name' => 'Conservancy', 'icon_class' => 'fas fa-leaf'],
            ['name' => 'Nature Walk', 'icon_class' => 'fas fa-tree'],
            ['name' => 'Snake Park', 'icon_class' => 'fas fa-snake'],
            ['name' => 'Zoo', 'icon_class' => 'fas fa-hippo'],

            // --- ARTS & CULTURE ---
            ['name' => 'Museum', 'icon_class' => 'fas fa-landmark'],
            ['name' => 'Art Gallery', 'icon_class' => 'fas fa-palette'],
            ['name' => 'Cultural Centre', 'icon_class' => 'fas fa-globe-africa'],
            ['name' => 'Cinema', 'icon_class' => 'fas fa-film'],
            ['name' => 'Maasai Market', 'icon_class' => 'fas fa-shopping-bag'],
            ['name' => 'Pottery Class', 'icon_class' => 'fas fa-hands'],

            // --- EVENTS & BUSINESS ---
            ['name' => 'Wedding Venue', 'icon_class' => 'fas fa-ring'],
            ['name' => 'Conference Centre', 'icon_class' => 'fas fa-briefcase'],
            ['name' => 'Team Building', 'icon_class' => 'fas fa-users'],
            ['name' => 'Religious Retreat', 'icon_class' => 'fas fa-place-of-worship'],

            // --- SERVICES ---
            ['name' => 'Spa', 'icon_class' => 'fas fa-spa'],
            ['name' => 'Massage', 'icon_class' => 'fas fa-hands-helping'],
            ['name' => 'Salon', 'icon_class' => 'fas fa-cut'],
            ['name' => 'Shopping Mall', 'icon_class' => 'fas fa-shopping-cart'],
            ['name' => 'Car Wash', 'icon_class' => 'fas fa-car'],
            ['name' => 'Airport', 'icon_class' => 'fas fa-plane'],
            
            // --- MISC ---
            ['name' => 'Pool Table / Billiards', 'slug' => 'pool-table', 'icon_class' => 'fas fa-dot-circle'],
            ['name' => 'Hidden Gems', 'slug' => 'hidden-gems', 'icon_class' => 'fas fa-gem'],
        ];

        foreach ($categories as $category) {
            $slug = $category['slug'] ?? Str::slug($category['name']);
            
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'icon_class' => $category['icon_class'] ?? 'fas fa-tag',
                    'parent_id' => null
                ]
            );
        }

        $this->command->info('Master Categories seeded and consolidated successfully.');
    }
}