<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. The Main Circuit / Itinerary
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Visuals
            $table->string('cover_image')->nullable();
            $table->string('theme_color')->default('#3b82f6'); // Default Blue
            
            // Meta
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->date('start_date')->nullable(); // Cached for easy sorting
            $table->date('end_date')->nullable();
            
            $table->timestamps();
        });

        // 2. The Timeline Stops (Legs)
        Schema::create('itinerary_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('itineraries')->onDelete('cascade');
            
            $table->string('title'); // e.g., "Thanksgiving, Karatina"
            $table->text('description')->nullable();
            
            // Timing
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            
            // Location
            $table->string('location_name')->nullable(); // Free text
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete(); // Linked directory item
            $table->foreignId('county_id')->nullable()->constrained('counties')->nullOnDelete();
            
            // Visuals
            $table->string('image_url')->nullable(); 
            
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // 3. Social: Participants (Join)
        Schema::create('itinerary_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('itineraries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->enum('status', ['going', 'interested', 'invited'])->default('going');
            
            $table->timestamps();
            
            $table->unique(['itinerary_id', 'user_id']);
        });
        
        // 4. Social: Likes/Saves (Simple Pivot)
        Schema::create('itinerary_likes', function (Blueprint $table) {
           $table->id();
           $table->foreignId('itinerary_id')->constrained('itineraries')->onDelete('cascade');
           $table->foreignId('user_id')->constrained()->onDelete('cascade');
           $table->timestamps();
           $table->unique(['itinerary_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary_likes');
        Schema::dropIfExists('itinerary_participants');
        Schema::dropIfExists('itinerary_stops');
        Schema::dropIfExists('itineraries');
    }
};
