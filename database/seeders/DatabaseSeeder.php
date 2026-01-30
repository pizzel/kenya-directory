<?php

namespace Database\Seeders; // <<<< CRUCIAL: THIS NAMESPACE MUST BE CORRECT

// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Optional
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder // <<<< CRUCIAL: CLASS NAME MUST BE CORRECT
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountySeeder::class,
            CategorySeeder::class,
            FacilitySeeder::class,
            TagSeeder::class,
            AdminUserSeeder::class,
			EventCategorySeeder::class,
            // Add other seeders here
        ]);

        // Example of using factories:
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}