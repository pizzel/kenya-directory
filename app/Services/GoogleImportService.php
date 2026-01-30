<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Category;
use App\Models\County;
use App\Models\Facility;
use App\Models\Tag;
use App\Models\User;
use App\Models\GoogleReview; // Ensure this model exists
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class GoogleImportService
{
    private ?string $apiKey;
    private ?User $defaultAdmin;
    private $allFacilities;
    private $allTags;
    private $allCounties;
    private array $googleTypeToIconMap = [
        'tourist_attraction' => 'fas fa-map-marker-alt', 'park' => 'fas fa-tree', 'zoo' => 'fas fa-hippo',
        'museum' => 'fas fa-landmark', 'art_gallery' => 'fas fa-paint-brush', 'restaurant' => 'fas fa-utensils',
        'cafe' => 'fas fa-coffee', 'food' => 'fas fa-utensils', 'lodging' => 'fas fa-bed',
        'hotel' => 'fas fa-concierge-bell', 'church' => 'fas fa-church', 'mosque' => 'fas fa-mosque',
        'hindu_temple' => 'fas fa-om', 'synagogue' => 'fas fa-star-of-david', 'shopping_mall' => 'fas fa-shopping-bag',
        'store' => 'fas fa-store', 'amusement_park' => 'fas fa-ferris-wheel', 'bowling_alley' => 'fas fa-bowling-ball',
        'movie_theater' => 'fas fa-film', 'night_club' => 'fas fa-music', 'bar' => 'fas fa-glass-martini-alt',
        'library' => 'fas fa-book', 'stadium' => 'fas fa-futbol', 'campground' => 'fas fa-campground',
        'spa' => 'fas fa-spa', 'gym' => 'fas fa-dumbbell', 'point_of_interest' => 'fas fa-star',
        'establishment' => 'fas fa-building',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
        $this->defaultAdmin = User::where('role', 'admin')->first();
        $this->allFacilities = Facility::all();
        $this->allTags = Tag::all();
        $this->allCounties = County::all();
    }

    public function import(string $query, ?string $countyName = null, ?string $activityName = null): array
    {
        if (!$this->apiKey) { return ['success' => false, 'message' => 'API Key not set.']; }
        if (!$this->defaultAdmin) { return ['success' => false, 'message' => 'No admin user found.']; }
        $fullQuery = $countyName ? "{$query} in {$countyName}, Kenya" : "{$query}, Kenya";
        $searchResponse = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
            'query' => $fullQuery, 'key' => $this->apiKey, 'region' => 'ke',
        ]);
        if (!$searchResponse->successful() || !$searchResponse->json('ok', true)) { return ['success' => false, 'message' => 'Google Search API failed.']; }
        $places = $searchResponse->json('results');
        $importedCount = 0;
        foreach ($places as $place) {
            if (empty($place['place_id'])) continue;
            try {
                $this->importPlaceDetails($place['place_id'], $countyName, $activityName, $query);
                $importedCount++;
                usleep(200000); // 0.2 seconds (5 businesses per second)
            } catch (\Exception $e) {
                Log::error("Failed to import place: {$place['name']}", [
                    'place_id' => $place['place_id'], 'error' => $e->getMessage()
                ]);
            }
        }
        return ['success' => true, 'message' => "Successfully imported or updated {$importedCount} businesses."];
    }
    
    public function importSingleByName(mixed $businessOrName, bool $skipImages = false): array
    {
        if (!$this->apiKey) return ['success' => false, 'message' => 'API Key not set.'];
        
        // 1. Determine Search Query
        if ($businessOrName instanceof Business) {
            $business = $businessOrName;
            $businessName = $business->name;
            // Use existing data to build a tight query
            $fullQuery = $business->county 
                ? "{$business->name}, {$business->county->name}, Kenya" 
                : "{$business->name}, Kenya";
        } else {
            // Fallback for legacy string calls
            $businessName = $businessOrName;
            $fullQuery = "{$businessName}, Kenya";
        }
        
        // 2. Find Place ID
        $findPlaceResponse = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
            'input' => $fullQuery, 
            'inputtype' => 'textquery', 
            'fields' => 'place_id', 
            'key' => $this->apiKey,
        ]);

        if (!$findPlaceResponse->successful() || $findPlaceResponse->json('status') !== 'OK') {
            return ['success' => false, 'message' => "Could not find match for '{$businessName}'."];
        }

        $candidates = $findPlaceResponse->json('candidates');
        if (empty($candidates)) {
            return ['success' => false, 'message' => "No candidates found for '{$businessName}'."];
        }
        
        $placeId = $candidates[0]['place_id'];
        
        try {
            // Pass the Business Object down to the details method
            $this->importPlaceDetails($placeId, $businessOrName, $skipImages);
            return ['success' => true, 'message' => "Successfully updated '{$businessName}'."];
        } catch (\Exception $e) {
            Log::error("Error importing '{$businessName}'", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => "Error updating '{$businessName}'."];
        }
    }

    public function importPlaceDetails(string $placeId, mixed $businessOrHint = null, bool $skipImages = false): void
    {
        // 1. Fetch Details
        $detailsResponse = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'name,formatted_address,geometry,website,international_phone_number,place_id,photos,types,adr_address,editorial_summary,opening_hours,reviews,rating,user_ratings_total', 
            'key' => $this->apiKey,
        ]);

        if (!$detailsResponse->successful() || !$detailsResponse->json('ok', true)) return;
        $details = $detailsResponse->json('result');
        if (empty($details)) return;

        // ... [Insert your Description Logic here if not already present] ...
        $description = null;
        if (!empty($details['editorial_summary']['overview'])) {
            $description = $details['editorial_summary']['overview'];
        } elseif (!empty($details['website'])) {
            $websiteDesc = $this->fetchWebsiteDescription($details['website']);
            if ($websiteDesc && strlen($websiteDesc) > 20) $description = $websiteDesc;
        }
        if (!$description && !empty($details['reviews'])) {
            $bestReviews = collect($details['reviews'])->where('rating', 5)->sortByDesc(fn($r) => strlen($r['text'] ?? ''));
            if ($bestReviews->first()) $description = '"' . Str::limit($bestReviews->first()['text'], 400) . '" - ' . $bestReviews->first()['author_name'] . ' (Google Review)';
        }

        // 2. IDENTIFY TARGET (Strict vs Legacy)
        $businessToUpdate = null;
        if ($businessOrHint instanceof Business) {
            $businessToUpdate = $businessOrHint; // STRICT: Updates existing row
        } else {
            $businessToUpdate = Business::firstOrNew(['slug' => Str::slug($details['name'])]); // LEGACY
        }

        // 3. PREPARE DATA (Requirement #3: Save Google Place ID)
        $businessToUpdate->google_place_id = $placeId; // <<< SAVING THE ID HERE
        
        $businessToUpdate->user_id = $this->defaultAdmin->id;
        $businessToUpdate->address = $details['formatted_address'] ?? null;
        $businessToUpdate->latitude = $details['geometry']['location']['lat'];
        $businessToUpdate->longitude = $details['geometry']['location']['lng'];
        $businessToUpdate->phone_number = $details['international_phone_number'] ?? null;
        $businessToUpdate->website = $details['website'] ?? null;
        $businessToUpdate->is_verified = true;
        
        // Save Ratings
        $businessToUpdate->google_rating = $details['rating'] ?? null;
        $businessToUpdate->google_rating_count = $details['user_ratings_total'] ?? 0;

        if ($description) {
            $businessToUpdate->description = $description;
        }

        // Only update name if it's a brand new record (to prevent slug changes on existing)
        if (!$businessToUpdate->exists) {
            $businessToUpdate->name = $details['name'];
        }
        
        $county = $this->matchToExistingCounty($details);
        if ($county) $businessToUpdate->county_id = $county->id;

        $businessToUpdate->save();

        // 4. SYNC RELATIONS
        $searchableText = strtolower($businessToUpdate->name . ' ' . ($description ?? '') . ' ' . implode(' ', $details['types'] ?? []));
        
        $this->syncActivities($businessToUpdate, $details['types'] ?? [], $searchableText);
        $this->syncFacilities($businessToUpdate, $searchableText);
        $this->syncTags($businessToUpdate, $searchableText);
        $this->syncGoogleReviews($businessToUpdate, $details['reviews'] ?? []);

        // 5. IMAGES (Requirement #2: Skip Images)
        if (!$skipImages) {
            $this->syncImages($businessToUpdate, $details['photos'] ?? []);
        } else {
            Log::info("Skipping images for '{$businessToUpdate->name}' (Cost Saving Mode).");
        }

        $this->syncSchedules($businessToUpdate, $details['opening_hours'] ?? null);
    }

    private function syncGoogleReviews(Business $business, array $reviews): void
    {
        if (empty($reviews)) return;
        // Clear old google reviews to refresh them
        $business->googleReviews()->delete();

        foreach ($reviews as $review) {
            $business->googleReviews()->create([
                'author_name' => $review['author_name'] ?? 'Google User',
                'author_url' => $review['author_url'] ?? null,
                'profile_photo_url' => $review['profile_photo_url'] ?? null,
                'rating' => $review['rating'] ?? 0,
                'text' => $review['text'] ?? null,
                'relative_time_description' => $review['relative_time_description'] ?? null,
                'time' => $review['time'] ?? now()->timestamp,
            ]);
        }
        Log::info("Synced " . count($reviews) . " Google reviews for '{$business->name}'");
    }

    private function matchToExistingCounty(array $googleDetails, ?string $countyHint = null, ?string $queryHint = null): ?County
    {
        $googleLocality = '';
        if (!empty($googleDetails['adr_address'])) {
            preg_match('/<span class="locality">(.*?)<\/span>/', $googleDetails['adr_address'], $matches);
            $googleLocality = strtolower($matches[1] ?? '');
        }
        if ($googleLocality) {
            foreach ($this->allCounties as $county) {
                if (str_contains(strtolower($county->name), $googleLocality)) return $county;
            }
        }
        if ($countyHint) {
            $hint = strtolower($countyHint);
            foreach ($this->allCounties as $county) {
                if (str_contains(strtolower($county->name), $hint)) return $county;
            }
        }
        if ($queryHint) {
            $hint = strtolower($queryHint);
             foreach ($this->allCounties as $county) {
                if (str_contains($hint, strtolower($county->name))) return $county;
            }
        }
        return null;
    }

    public function syncActivities(Business $business, array $googleTypes, string $searchableText, ?string $primaryActivityName = null): void
    {
        $categoryIds = [];
        $allActivities = Category::all();

        if ($primaryActivityName) {
            $primaryActivity = $allActivities->firstWhere('name', $primaryActivityName);
            if (!$primaryActivity) {
                $primaryActivity = Category::create([
                    'name' => $primaryActivityName,
                    'slug' => Str::slug($primaryActivityName),
                    'icon_class' => 'fas fa-star' 
                ]);
                $allActivities->push($primaryActivity);
            }
            $categoryIds[$primaryActivity->id] = true;
        }

        foreach ($googleTypes as $type) {
            $name = ucwords(str_replace('_', ' ', $type));
            $slug = Str::slug($name);
            $category = $allActivities->firstWhere('slug', $slug);
            
            if (!$category) {
                $iconClass = $this->googleTypeToIconMap[$type] ?? 'fas fa-map-signs'; 
                $category = Category::create([
                    'name' => $name,
                    'slug' => $slug,
                    'icon_class' => $iconClass 
                ]);
                $allActivities->push($category);
            }
            $categoryIds[$category->id] = true;
        }

        foreach ($allActivities as $activity) {
            $keyword = str_replace('-', '', Str::singular(strtolower($activity->name)));
            if (str_contains($searchableText, $keyword)) {
                $categoryIds[$activity->id] = true;
            }
        }

        if (!empty($categoryIds)) {
            $business->categories()->sync(array_keys($categoryIds));
        }
    }

    public function syncFacilities(Business $business, string $searchableText): void
    {
        $facilityIds = [];
        foreach ($this->allFacilities as $facility) {
            $keyword = strtolower($facility->name);
            if ($keyword === 'free wi-fi') $keyword = 'wifi';
            if ($keyword === 'parking available') $keyword = 'parking';
            if ($keyword === 'pet-friendly') $keyword = 'pet';
            if ($keyword === 'family-friendly') $keyword = 'family';
            if ($keyword === 'kids play area') $keyword = 'play area';
            if ($keyword === 'wheelchair accessible') $keyword = 'wheelchair';
            if (str_contains($searchableText, $keyword)) $facilityIds[] = $facility->id;
        }
        if (!empty($facilityIds)) $business->facilities()->sync($facilityIds);
    }

    public function syncTags(Business $business, string $searchableText): void
    {
        $tagIds = [];
        foreach ($this->allTags as $tag) {
            $keyword = strtolower($tag->name);
            if (str_contains($searchableText, $keyword)) $tagIds[] = $tag->id;
        }
        if (!empty($tagIds)) $business->tags()->sync($tagIds);
    }

    private function syncImages(Business $business, array $photos): void
    {
        if (empty($photos)) {
            Log::info("No Google photos found for '{$business->name}'. Skipping image sync.");
            return;
        }

        $limit = 10;
        $business->clearMediaCollection('images');
        Log::info("Cleared existing media for '{$business->name}' before import.");

        $photosToDownload = array_slice($photos, 0, $limit);

        foreach ($photosToDownload as $index => $photoData) {
            $photoRef = $photoData['photo_reference'] ?? null;
            if (!$photoRef) {
                continue;
            }

            try {
                $imageUrl = "https://maps.googleapis.com/maps/api/place/photo?maxwidth=1920&photo_reference={$photoRef}&key={$this->apiKey}";
                
                $business->addMediaFromUrl($imageUrl)
                    ->withCustomProperties(['caption' => strip_tags($photoData['html_attributions'][0] ?? '')])
                    ->toMediaCollection('images');

                Log::info("Successfully added image #{$index} for '{$business->name}'.");
                sleep(1);

            } catch (\Exception $e) {
                Log::error("Exception while importing Google image for business #{$business->id}", [
                    'photo_reference' => $photoRef,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function syncSchedules(Business $business, ?array $openingHours): void
    {
        $business->schedules()->delete();
        if (!empty($openingHours['periods'])) {
            $dayMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($openingHours['periods'] as $period) {
                if (isset($period['open']['day'])) {
                    $dayOfWeek = $dayMap[$period['open']['day']];
                    $business->schedules()->create([
                        'day_of_week' => $dayOfWeek,
                        'open_time' => substr_replace($period['open']['time'], ':', 2, 0),
                        'close_time' => isset($period['close']) ? substr_replace($period['close']['time'], ':', 2, 0) : '23:59',
                        'is_closed_all_day' => false,
                    ]);
                }
            }
        } else {
            $defaultDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($defaultDays as $day) {
                $business->schedules()->create([
                    'day_of_week' => $day,
                    'open_time' => '08:30:00',
                    'close_time' => '18:00:00',
                    'is_closed_all_day' => false,
                ]);
            }
        }
    }

    private function fetchWebsiteDescription(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->timeout(3)->get($url); // Increased timeout to 15s

            if ($response->failed()) return null;

            $html = $response->body();
            if (empty($html)) return null;

            $crawler = new Crawler($html);

            $description = null;
            
            // 1. Try standard meta description
            $meta = $crawler->filter('meta[name="description"]');
            if ($meta->count() > 0) {
                $description = $meta->attr('content');
            }

            // 2. Try OpenGraph description
            if (empty($description)) {
                $ogMeta = $crawler->filter('meta[property="og:description"]');
                if ($ogMeta->count() > 0) {
                    $description = $ogMeta->attr('content');
                }
            }

            return $description ? html_entity_decode(Str::limit($description, 500)) : null;

        } catch (\Exception $e) {
            Log::warning("Website fetch error: " . $e->getMessage());
            return null;
        }
    }
}