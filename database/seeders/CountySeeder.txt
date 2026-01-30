<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\County; // Import the model
use Illuminate\Support\Facades\DB; // For DB facade if needed
use Illuminate\Support\Str;

class CountySeeder extends Seeder
{
    public function run(): void
    {
          $counties = [
            ['name' => 'Mombasa'], ['name' => 'Kwale'], ['name' => 'Kilifi'],
            ['name' => 'Tana River'], ['name' => 'Lamu'], ['name' => 'Taita-Taveta'],
            ['name' => 'Garissa'], ['name' => 'Wajir'], ['name' => 'Mandera'],
            ['name' => 'Marsabit'], ['name' => 'Isiolo'], ['name' => 'Meru'],
            ['name' => 'Tharaka-Nithi'], ['name' => 'Embu'], ['name' => 'Kitui'],
            ['name' => 'Machakos'], ['name' => 'Makueni'], ['name' => 'Nyandarua'],
            ['name' => 'Nyeri'], ['name' => 'Kirinyaga'], ['name' => 'Murang\'a'], // Escaped apostrophe
            ['name' => 'Kiambu'], ['name' => 'Turkana'], ['name' => 'West Pokot'],
            ['name' => 'Samburu'], ['name' => 'Trans Nzoia'], ['name' => 'Uasin Gishu'],
            ['name' => 'Elgeyo-Marakwet'], ['name' => 'Nandi'], ['name' => 'Baringo'],
            ['name' => 'Laikipia'], ['name' => 'Nakuru'], ['name' => 'Narok'],
            ['name' => 'Kajiado'], ['name' => 'Kericho'], ['name' => 'Bomet'],
            ['name' => 'Kakamega'], ['name' => 'Vihiga'], ['name' => 'Bungoma'],
            ['name' => 'Busia'], ['name' => 'Siaya'], ['name' => 'Kisumu'],
            ['name' => 'Homa Bay'], ['name' => 'Migori'], ['name' => 'Kisii'],
            ['name' => 'Nyamira'], ['name' => 'Nairobi City'],
        ];

        foreach ($counties as $countyData) {
            County::firstOrCreate(
                ['name' => $countyData['name']], // Find by name
                ['slug' => Str::slug($countyData['name'])] // Add slug on creation
            );
        }
    }
}