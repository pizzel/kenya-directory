<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Make sure the User model is imported
use Illuminate\Support\Facades\Hash; // For hashing the password

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'dnl.nganga@gmail.com'], // Condition to check if user exists
            [
                'name' => 'Daniel Nganga (Admin)', // Or any name you prefer
                'password' => Hash::make('12345'), // HASH THE PASSWORD!
                'role' => 'admin', // Ensure this role matches what Filament expects or what your canAccessPanel method checks
                'email_verified_at' => now(), // Mark email as verified for admin
            ]
        );

        // You can add more users here if needed
        // User::firstOrCreate(
        //     ['email' => 'businessowner@example.com'],
        //     [
        //         'name' => 'Test Business Owner',
        //         'password' => Hash::make('password'),
        //         'role' => 'business_owner',
        //         'email_verified_at' => now(),
        //     ]
        // );
    }
}