<?php

namespace App\Services;

use App\Models\Business;
use App\Models\DiscoveryCollection;
use Illuminate\Support\Str;

class SemanticSEOService
{
    /**
     * Map categories to their Semantic Clusters and Associated Words.
     * Based on Google's Semantic Model recommendations.
     */
    public static function getSemanticClusters(): array
    {
        return [
            'adventure' => [
                'keywords' => ['adrenaline', 'safety briefing', 'harness', 'outdoor activities', 'excursion', 'thrilling', 'scenic', 'expedition'],
                'predicate' => 'offers exhilarating outdoor excursions including',
                'schema' => 'TouristAttraction'
            ],
            'game-drive' => [
                'keywords' => ['Big Five', 'game drives', 'migration', 'conservation', 'bush breakfast', '4x4 vehicles', 'savannah', 'wildlife sanctuary'],
                'predicate' => 'provides immersive wildlife experiences and conservation-focused game drives in the heart of',
                'schema' => 'TouristAttraction'
            ],
            'restaurant' => [
                'keywords' => ['authentic cuisine', 'local flavors', 'fine dining', 'nyama choma', 'ambiance', 'culinary', 'gastronomy', 'chef-curated'],
                'predicate' => 'serves authentic Kenyan flavors and fine dining options featuring',
                'schema' => 'FoodEstablishment'
            ],
            'hotel' => [
                'keywords' => ['hospitality', 'amenities', 'luxury accommodation', 'boutique stay', 'wellness', 'retreat', 'staycation', 'concierge'],
                'predicate' => 'offers premium luxury accommodation and world-class hospitality in',
                'schema' => 'Hotel'
            ],
            'conservancy' => [
                'keywords' => ['biodiversity', 'flora and fauna', 'panoramic views', 'ecosystem', 'wildlife', 'conservation', 'indigenous', 'sanctuary'],
                'predicate' => 'protects a diverse ecosystem of indigenous flora and fauna with panoramic views of',
                'schema' => 'TouristAttraction'
            ],
            'hiking' => [
                'keywords' => ['trailhead', 'elevation', 'difficulty level', 'gear', 'hiking guide', 'summit', 'trekking', 'nature trail'],
                'predicate' => 'features challenging trekking trails with varying elevation levels leading to the summit of',
                'schema' => 'TouristAttraction'
            ],
            // --- NEW CLUSTERS FOR TOP COLLECTIONS ---
            'night-club' => [
                'keywords' => ['live DJ', 'VIP section', 'cocktail menu', 'dance floor', 'nightlife', 'afrobeats', 'security', 'party ambiance'],
                'predicate' => 'provides an electrifying nightlife experience with world-class entertainment and a vibrant',
                'schema' => 'NightClub'
            ],
            'bar-lounge' => [
                'keywords' => ['craft cocktails', 'chill vibe', 'happy hour', 'rooftop view', 'mixology', 'socializing', 'scenic sunset', 'bar bites'],
                'predicate' => 'offers a relaxed social atmosphere with expert mixology and panoramic views for a perfect',
                'schema' => 'BarOrPub'
            ],
            'swimming-pool' => [
                'keywords' => ['heated pool', 'sunbeds', 'family-friendly', 'lap pool', 'poolside service', 'clean water', 'changing rooms', 'lifeguard'],
                'predicate' => 'features a pristine aquatic facility ideal for relaxation and fitness, complete with',
                'schema' => 'PublicSwimmingPool'
            ],
            'conference-centre' => [
                'keywords' => ['state-of-the-art technology', 'high-speed wifi', 'breakout rooms', 'corporate catering', 'ample parking', 'plenary hall', 'accessibility', 'business support'],
                'predicate' => 'hosts world-class corporate events and summits, equipped with modern logistics and',
                'schema' => 'EventVenue'
            ],
            'resort' => [
                'keywords' => ['all-inclusive', 'beachfront access', 'spa treatments', 'kids club', 'luxury suites', 'resort amenities', 'relaxing getaway', 'ocean view'],
                'predicate' => 'delivers a comprehensive vacation experience with exclusive access to premium amenities and',
                'schema' => 'Resort'
            ],
            'camping' => [
                'keywords' => ['under the stars', 'bonfire', 'secure campsite', 'tent pitch', 'nature sounds', 'glamping options', 'outdoor cooking', 'clean washrooms'],
                'predicate' => 'offers an immersive outdoor living experience where you can sleep under the stars in a',
                'schema' => 'Campground'
            ],
            'cottage' => [
                'keywords' => ['self-catering', 'private garden', 'homely feel', 'fully furnished', 'peaceful retreat', 'family stay', 'kitchenette', 'privacy'],
                'predicate' => 'provides a private, home-away-from-home sanctuary perfect for families seeking a',
                'schema' => 'LodgingBusiness'
            ],
        ];
    }

    /**
     * Generate a Semantic Triple (Subject-Predicate-Object) for a business.
     */
    public function generateContextSummary(Business $business): string
    {
        $clusters = self::getSemanticClusters();
        
        // Find the first category that actually has a semantic cluster defined
        $primaryCategory = $business->categories->first(function($cat) use ($clusters) {
            return isset($clusters[$cat->slug]);
        });

        if (!$primaryCategory) return strip_tags($business->about_us ?: $business->description);

        $cluster = $clusters[$primaryCategory->slug];
        $location = $business->county->name ?? 'Kenya';
        
        if ($cluster) {
            $keywords = collect($cluster['keywords'])->random(2)->join(', ');
            return "{$business->name} in {$location} {$cluster['predicate']} {$keywords}. This {$primaryCategory->name} is highly recommended for travelers seeking " . collect($cluster['keywords'])->random(1)->first() . " in the region.";
        }

        return strip_tags(Str::limit($business->about_us, 160));
    }

    /**
     * Generate Schema.org JSON-LD for a Business.
     */
    public function generateBusinessSchema(Business $business): array
    {
        $clusters = self::getSemanticClusters();
        
        // Find the first category that has a specific schema mapping
        $primaryCategory = $business->categories->first(function($cat) use ($clusters) {
            return isset($clusters[$cat->slug]);
        });

        $type = 'LocalBusiness';
        
        if ($primaryCategory) {
            $type = $clusters[$primaryCategory->slug]['schema'] ?? 'LocalBusiness';
        }

        $schema = [
            "@context" => "https://schema.org",
            "@type" => $type,
            "name" => $business->name,
            "description" => strip_tags($business->about_us ?: $business->description),
            "url" => route('listings.show', $business->slug),
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => $business->county->name ?? 'Kenya',
                "addressCountry" => "KE"
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => $business->google_rating ?? 5.0,
                "reviewCount" => $business->reviews_count ?: 1
            ],
        ];

        if ($business->getImageUrl()) {
            $schema['image'] = $business->getImageUrl();
        }

        return $schema;
    }

    /**
     * Generate Schema.org JSON-LD for a Collection/Guide.
     */
    public function generateCollectionSchema(DiscoveryCollection $collection, $businesses): array
    {
        return [
            "@context" => "https://schema.org",
            "@type" => "ItemPage",
            "mainEntity" => [
                "@type" => "ItemList",
                "name" => $collection->title,
                "description" => strip_tags($collection->description),
                "itemListElement" => $businesses->map(fn($b, $i) => [
                    "@type" => "ListItem",
                    "position" => $i + 1,
                    "url" => route('listings.show', $b->slug),
                    "name" => $b->name
                ])->toArray()
            ]
        ];
    }
}
