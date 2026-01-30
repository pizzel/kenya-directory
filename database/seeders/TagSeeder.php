<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Hidden Gem'],
            ['name' => 'Serene Environment'],
            ['name' => 'Family Friendly'],
            ['name' => 'Romantic'],
            ['name' => 'Pocket Friendly'],
            ['name' => 'Luxury'],
            ['name' => 'Scenic View'],
            ['name' => 'Trendy Vibe'],
            ['name' => 'Good for Groups'],
            ['name' => 'Work Friendly'],
            ['name' => 'Pet Friendly'],
            ['name' => 'Solo Traveller Friendly'],
            ['name' => 'Alcohol Free'],
            ['name' => 'Nyama Choma'],
            ['name' => 'Halal Options'],
            ['name' => 'Vegetarian Options'],
            ['name' => 'Rustic'],
            ['name' => '4x4 Required'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => Str::slug($tag['name'])], $tag);
        }
    }
}