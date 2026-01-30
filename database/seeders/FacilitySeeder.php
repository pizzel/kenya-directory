<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // COMFORT & PAIN POINTS
            ['name' => 'Hot Shower', 'icon_class' => 'fas fa-shower'],
            ['name' => 'Hot Water Bottles', 'icon_class' => 'fas fa-water'],
            ['name' => 'Heated Pool', 'icon_class' => 'fas fa-temperature-hot'],
            ['name' => 'Mosquito Nets', 'icon_class' => 'fas fa-shield-alt'],
            ['name' => 'Air Conditioning', 'icon_class' => 'fas fa-snowflake'],
            ['name' => 'Backup Generator', 'icon_class' => 'fas fa-bolt'],
            ['name' => 'Solar Power', 'icon_class' => 'fas fa-sun'],

            // KIDS & FUN
            ['name' => 'Bouncing Castle', 'icon_class' => 'fas fa-chess-rook'],
            ['name' => 'Trampoline', 'icon_class' => 'fas fa-arrow-up'],
            ['name' => 'Kids Play Area', 'icon_class' => 'fas fa-child'],
            ['name' => 'Swimming Pool', 'icon_class' => 'fas fa-swimmer'],
            ['name' => 'Baby Changing Station', 'icon_class' => 'fas fa-baby'],

            // ACCESS & UTILITY
            ['name' => 'M-Pesa Accepted', 'icon_class' => 'fas fa-mobile-alt'],
            ['name' => 'Prayer Room', 'icon_class' => 'fas fa-praying-hands'],
            ['name' => 'Wheelchair Ramp', 'icon_class' => 'fas fa-wheelchair'],
            ['name' => 'Free WiFi', 'icon_class' => 'fas fa-wifi'],
            ['name' => 'Secure Parking', 'icon_class' => 'fas fa-parking'],
            ['name' => 'Clean Restrooms', 'icon_class' => 'fas fa-toilet'],
            ['name' => 'Kitchenette', 'icon_class' => 'fas fa-utensils'],

            // LUXURY & NICHE
            ['name' => 'Helipad', 'icon_class' => 'fas fa-helicopter'],
            ['name' => 'Airstrip Nearby', 'icon_class' => 'fas fa-plane-arrival'],
            ['name' => 'Private Plunge Pool', 'icon_class' => 'fas fa-water'],
            ['name' => 'Bathtub', 'icon_class' => 'fas fa-bath'],
            ['name' => 'Fireplace', 'icon_class' => 'fas fa-fire'],
            ['name' => 'Electric Fence', 'icon_class' => 'fas fa-bolt'],
            ['name' => 'Pool Table', 'icon_class' => 'fas fa-dot-circle'],
            ['name' => 'Live Band', 'icon_class' => 'fas fa-music'],
            ['name' => 'Sports Screens', 'icon_class' => 'fas fa-tv'],
            ['name' => 'Outdoor Seating', 'icon_class' => 'fas fa-chair'],
            ['name' => 'Garden', 'icon_class' => 'fas fa-tree'],
            ['name' => 'Conference Hall', 'icon_class' => 'fas fa-briefcase'],
            ['name' => 'Buffet', 'icon_class' => 'fas fa-utensils'],
        ];

        foreach ($facilities as $fac) {
            Facility::updateOrCreate(['slug' => Str::slug($fac['name'])], $fac);
        }
    }
}