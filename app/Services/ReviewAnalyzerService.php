<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ReviewAnalyzerService
{
    protected Collection $allCategories;
    protected Collection $allFacilities;
    protected Collection $allTags;

    public function __construct()
    {
        $this->allCategories = Category::all()->keyBy('slug');
        $this->allFacilities = Facility::all()->keyBy('slug');
        $this->allTags       = Tag::all()->keyBy('slug');
    }

    /**
     * MAIN ENTRY POINT
     */
    /**
     * Main Entry Point
     * UPDATED: Balanced Scoring System to preserve signals while blocking noise.
     */
    public function analyzeBusiness(Business $business): void
    {
        $nameText = strtolower($business->name);
        // Combine description and strip tags
        $aboutText = strtolower(strip_tags($business->about_us . ' ' . $business->description));
        // Combine all reviews
        $reviewsText = strtolower($business->googleReviews->pluck('text')->filter()->join(' '));
        
        // Create one giant text blob for easier context checking
        $fullText = $nameText . ' ' . $aboutText . ' ' . $reviewsText;

        $mappings = $this->getCombinedMappings(); 

        $catIds = $this->calculateWeightedMatches($nameText, $aboutText, $reviewsText, $mappings['categories'], $this->allCategories, $business->name);
        $facIds = $this->calculateWeightedMatches($nameText, $aboutText, $reviewsText, $mappings['facilities'], $this->allFacilities, $business->name);
        $tagIds = $this->calculateWeightedMatches($nameText, $aboutText, $reviewsText, $mappings['tags'], $this->allTags, $business->name);

        // ---------------------------------------------------------
        // 🎱 VS 🏊 THE "POOL" CONTEXT RESOLVER
        // ---------------------------------------------------------
        
        // Check if the standalone word "pool" exists (isolated from "pool table" or "swimming pool")
        // We use regex to find 'pool' BUT negative lookaround to ensure it's not 'pool table'
        // This regex means: Find "pool" where it is NOT followed immediately by " table"
        $hasAmbiguousPool = preg_match('/\bpool\b(?! table)/i', $fullText);
        $hasPoolTableExplicit = preg_match('/\b(pool table|billiards|snooker)\b/i', $fullText);

        if ($hasAmbiguousPool) {
            // IDs for your specific categories (Update these IDs to match your DB)
            $catSwimmingId = $this->allCategories['swimming']->id ?? null; // e.g., 5
            $facSwimmingId = $this->allFacilities['swimming-pool']->id ?? null; 
            $facPoolTableId = $this->allFacilities['pool-table']->id ?? null; // e.g., 20

            // CONTEXT 1: If it's Accommodation (Hotel, Resort, Camp) -> It's a SWIMMING POOL
            // (Unless they explicitly said "pool table" elsewhere, handled by weighted match)
            $isAccommodation = $business->categories->whereIn('slug', ['hotel', 'resort', 'safari-camp', 'villa', 'airbnb'])->isNotEmpty();
            
            if ($isAccommodation) {
                if ($catSwimmingId) $catIds[] = $catSwimmingId;
                if ($facSwimmingId) $facIds[] = $facSwimmingId;
            }

            // CONTEXT 2: If it's a Night Club / Bar -> It's likely a POOL TABLE
            // But be careful: Rooftop bars often have swimming pools.
            $isNightlife = $business->categories->whereIn('slug', ['bar', 'night-club', 'lounge', 'sports-bar'])->isNotEmpty();
            $isRooftop = str_contains($fullText, 'rooftop');

            if ($isNightlife && !$isAccommodation && !$isRooftop) {
                // If it's a bar (not a hotel, not a rooftop) and they say "pool", it's likely 8-ball.
                if ($facPoolTableId) $facIds[] = $facPoolTableId;
            }
            
            // CONTEXT 3: Explicit Clues in text
            // If text contains "water", "swim", "towel", "cold", "warm" near "pool" -> Swimming
            if (preg_match('/\b(water|swim|wet|towel|sunbed|cold|warm|dip)\b/i', $fullText)) {
                if ($catSwimmingId) $catIds[] = $catSwimmingId;
                if ($facSwimmingId) $facIds[] = $facSwimmingId;
            }
            
            // CONTEXT 4: Explicit Clues for Table
            // If text contains "cue", "balls", "game", "play" -> Table
            if (preg_match('/\b(cue|stick|balls|game|play)\b/i', $fullText)) {
                if ($facPoolTableId) $facIds[] = $facPoolTableId;
            }
        }
        
        // ---------------------------------------------------------
        
        if (!empty($catIds)) $business->categories()->syncWithoutDetaching(array_unique($catIds));
        if (!empty($facIds)) $business->facilities()->syncWithoutDetaching(array_unique($facIds));
        if (!empty($tagIds)) $business->tags()->syncWithoutDetaching(array_unique($tagIds));
    }

	/**
     * SIMPLIFIED SCORING ENGINE (With Regex Lookahead for "Pool")
     */
    private function calculateWeightedMatches(
        string $name, 
        string $about, 
        string $reviews, 
        array $mapping, 
        Collection $dbRecords, 
        string $businessName,
        string $typeLabel = 'ITEM'
    ): array
    {
        $foundIds = [];

        foreach ($mapping as $slug => $keywords) {
            if (!isset($dbRecords[$slug])) continue;

            $isDebugTarget = (str_contains($slug, 'pool') || str_contains($slug, 'swim') || str_contains($slug, 'billiard')) 
                             && str_contains(strtolower($businessName), 'playland');
            
            if ($isDebugTarget) {
                Log::info("🎱🔍 [$typeLabel] CHECKING SLUG: [$slug] for Business: [$businessName]");
            }

            $totalScore = 0;
            $scoreBreakdown = []; 

            foreach ($keywords as $k) {
                $k = trim(strtolower($k));
                if (empty($k)) continue;

                $variants = $this->getVariants($k);
                // Create the safe regex string for variants (e.g. pool|pools)
                $variantsRegex = $variants->map(fn($v) => preg_quote($v, '/'))->implode('|');
                
                // -------------------------------------------------------------
                // ⚡️ SPECIAL HANDLING FOR "POOL" ⚡️
                // -------------------------------------------------------------
                // If the keyword is "pool", we specifically ban it if it is 
                // followed immediately by "table" (with space or hyphen).
                if ($k === 'pool') {
                    // (?![-\s]*table) -> Negative Lookahead: Do not match if followed by "-" or " " and "table"
                    $pattern = '/\b(' . $variantsRegex . ')\b(?![-\s]*table)/i';
                } else {
                    // Standard matching for everything else
                    $pattern = '/\b(' . $variantsRegex . ')\b/i';
                }
                // -------------------------------------------------------------

                // 1. Name Match
                if (preg_match($pattern, $name)) {
                    $totalScore += 100;
                    $scoreBreakdown[] = "Name match: '$k' (+100)";
                }

                // 2. About Match
                if (preg_match($pattern, $about)) {
                    $totalScore += 50;
                    $scoreBreakdown[] = "About match: '$k' (+50)";
                }

                // 3. Review Matches
                $reviewMentions = preg_match_all($pattern, $reviews, $matches);
                if ($reviewMentions > 0) {
                    $points = $reviewMentions * 25;
                    $totalScore += $points;
                    $scoreBreakdown[] = "Review mentions ($reviewMentions) of '$k' (+$points)";
                }
                
                // 4. Phrase Bonus
                if ((str_contains($k, ' ') || str_contains($k, '-')) && preg_match($pattern, $about . ' ' . $reviews)) {
                    $totalScore += 20; 
                    $scoreBreakdown[] = "Phrase bonus '$k' (+20)";
                }

                if ($isDebugTarget && $totalScore > 0) {
                     Log::info("   👉 Found '$k' | Score: $totalScore | Breakdown: " . implode(', ', $scoreBreakdown));
                }

                if ($totalScore >= 40) {
                    $foundIds[] = $dbRecords[$slug]->id;
                    if ($isDebugTarget) {
                        Log::info("   ✅ [$typeLabel] MATCHED! Assigned ID: " . $dbRecords[$slug]->id);
                    }
                    break; 
                }
            }
        }

        return $foundIds;
    }

    /**
     * Helper to detect if a "pool" mention is actually about swimming
     */
    private function isSwimmingContext(string $text, string $keyword): bool
    {
        // Words that appear before "pool" indicating it's NOT a table
        // e.g. "swimming pool", "kids pool", "heated pool", "slide into the pool"
        $swimmingIndicators = [
            'swimming', 'swim', 'lap', 'kids', 'baby', 'heated', 'plunge', 
            'infinity', 'indoor', 'outdoor', 'slides', 'water', 'clean'
        ];
        
        $pattern = '/(' . implode('|', $swimmingIndicators) . ')\s*(\b' . preg_quote($keyword, '/') . '\b)/i';
        
        // Checks if text contains "swimming pool", "kids pool", etc.
        return (bool) preg_match($pattern, $text);
    }

    /**
     * THE DATA-BACKED THESAURUS
     */
/**
     * THE DATA-BACKED THESAURUS (UPDATED & MERGED)
     */
    protected function getMappings(): array
    {
        return [
			'categories' => [
				'airbnb' => [
					'airbnb',
					'homestay',
					'short stay',
					'vacation rental'
				],
				'airport' => [
					'airport',
					'airstrip',
					'domestic airport'
				],
				'amusement-park' => [
					'amusement park',
					'fun fair',
					'rides',
					'theme park'
				],
				'aquapark' => [
					'aqua park',
					'water slides',
					'splash park',
					'water park',
					'wave pool',
					'water pool',
					'swimming pool', // Primary
                    'swimming',      // Safe
                    'lap pool',
                    'pool',
                    'kids pool',
                    'baby pool',
                    'heated pool',
                    'plunge pool',
                    'dip in the water',
                    'poolside',      // Usually implies swimming
                    'infinity pool'
				],
				'archery' => [
					'archery',
					'bow and arrow',
					'target shooting'
				],
				'art-gallery' => [
					'art gallery',
					'art showcase',
					'exhibition',
					'paintings',
					'paint',
					'paint-sip',
					'art class',
					'paint and sip',
					'painting',
					'sip and paint'
				],
				'bakery' => [
					'bakery',
					'bread',
					'cakes',
					'croissants',
					'fresh bread',
					'pastries'
				],
				'bank' => [
					'atm',
					'bank',
					'banking'
				],
				'bar-lounge' => [
					'bar',
					'chill spot',
					'cocktails',
					'pub',
					'alcohol',
					'tavern',
					'lounge'
				],
				'basketball' => [
					'basketball',
					'basketball court',
					'hoops'
				],
				'bird-watching' => [
					'bird photography',
					'bird watching',
					'birding',
					'ornithology'
				],
				'boat-riding' => [
					'boat cruise',
					'boat ride',
					'boat trip',
					'canoe',
					'lake ride'
				],
				'bouncing-castle' => [
					'bouncing castle',
					'bouncy castle',
					'inflatable',
					'jumping castle'
				],
				'bowling-alley' => [
					'bowling',
					'bowling alley',
					'ten pin bowling'
				],
				'cafe' => [
					'bistro',
					'cafe',
					'cappuccino',
					'coffee house',
					'coffee shop',
					'latte'
				],
				'camel-riding' => [
					'camel ride',
					'camel riding',
					'camels'
				],
				'campground' => [
					'campground',
					'camping site',
					'campsite',
					'pitch your tent'
				],
				'camping' => [
					'bush camping',
					'camp site',
					'camping',
					'outdoor camping',
					'overnight camping',
					'tents'
				],
				'car-repair' => [
					'auto repair',
					'car repair',
					'garage',
					'mechanic'
				],
				'car-wash' => [
					'car wash',
					'vehicle cleaning'
				],
				'casino' => [
					'betting',
					'casino',
					'gambling',
					'slots'
				],
				'church' => [
					'cathedral',
					'chapel',
					'church',
					'worship'
				],
				'cinema' => [
					'cinema',
					'film',
					'imax',
					'movie theatre',
					'movies',
					'theatre'
				],
				'coffee-shop' => [
					'bistro',
					'cafe',
					'coffee shop',
					'espresso',
					'latte'
				],
				'conference-centre' => [
					'business meeting',
					'conference',
					'conference centre',
					'convention center',
					'corporate meeting',
					'meeting hall',
					'seminar',
					'workshop venue'
				],
				'conservancy' => [
					'conservancy',
					'nature reserve',
					'private conservancy',
					'protected area',
					'wildlife reserve'
				],
				'cottage' => [
					'cottage',
					'country house'
				],
				'cultural-centre' => [
					'bomas',
					'cultural centre',
					'culture',
					'heritage centre',
					'traditional village',
					'traditions'
				],
				'cycling' => [
                    'cycling', 'biking', 'sky biking', 'sky-biking', // Added sky biking
                    'bicycle ride'
                ],
				'dhow-cruise' => [
					'dhow cruise',
					'ocean cruise',
					'sunset cruise',
					'swahili boat',
					'traditional dhow'
				],
				'dolphins' => [
					'dolphin watching',
					'dolphins',
					'marine wildlife'
				],
				'escape-room' => [
					'escape room',
					'locked',
					'mystery room',
					'puzzle'
				],
				'event-venue' => [
					'event venue',
					'events place',
					'function hall',
					'party venue'
				],
				'fast-food' => [
					'burgers',
					'fast food',
					'fries',
					'pizza',
					'quick bites',
					'takeaway'
				],
				'fishing' => [
					'catch fish',
					'fish',
					'fishing',
					'sport fishing',
					'trout fishing'
				],
				
				'game-drive' => [
					'animals',
					'big five',
					'game drive',
					'game viewing',
					'lions',
					'safari',
					'safari drive',
					'wildlife safari'
				],
				'gaming-arcade' => [
					'arcade games',
					'gaming arcade',
					'video games'
				],
				'glamping' => [
					'eco camp',
					'glamping',
					'luxury tent'
				],
				'glass-bottom-boat' => [
					'glass boat',
					'glass bottom',
					'marine viewing',
					'view the fish'
				],
				'go-karting' => [
					'go kart',
					'gp karting',
					'kart racing',
					'karting',
					'race track'
				],
				'golfing' => [
					'18 holes',
					'caddy',
					'driving range',
					'golf club',
					'golf course',
					'golf tournament',
					'tee off'
				],
				'gym' => [
					'fitness',
					'gym',
					'training',
					'weights',
					'workout'
				],
				
				'high-ropes' => [ // Make sure you have this category
                    'high ropes', 'ropes course', 'challenge course', 
                    'obstacle course', 'giant swing' // Added giant swing here or distinct category
                ],
				'hiking' => [
					'climbing',
					'climbs',
					'hike',
					'hikes',
					'hiking',
					'mountain climbing',
					'climb',
					'hike',
					'mountain hike',
					'nature trail',
					'peak',
					'summit',
					'trail',
					'trekking',
					'walk in the forest'
				],
				'hindu-temple' => [
					'hindu temple',
					'mandir',
					'temple'
				],
				'horse-riding' => [
					'equestrian',
					'horse riding',
					'horseback',
					'pony rides'
				],
				'hospital' => [
					'clinic',
					'doctors office',
					'emergency',
					'emergency room',
					'hospital',
					'medical center'
				],
				'hostel' => [
					'backpackers',
					'dorm',
					'hostel'
				],
				'hotel' => [
					'accommodation',
					'hotel',
					'lodging',
					'rooms',
					'stay'
				],
				'ice-skating' => [
					'ice rink',
					'ice skating'
				],
				'indoor-play-area' => [
					'indoor play',
					'indoor playground',
					'kids indoor',
					'soft play'
				],
				'jet-skiing' => [
					'jet ski',
					'jetski',
					'water bike'
				],
				'karaoke' => [
					'karaoke',
					'karaoke night',
					'sing along',
					'singing'
				],
				'kayaking' => [
					'kayak',
					'kayaking',
					'lake kayaking',
					'river kayaking'
				],
				'kids-park' => [
					'bouncing castle',
					'funland',
					'kids park',
					'kids play',
					'play area',
					'slides'
				],
				'kids-play-area' => [
					'kids play area',
					'play area',
					'playground',
					'sand pit',
					'slides',
					'swings'
				],
				'kitesurfing' => [
					'kite',
					'kite school',
					'kite surfing',
					'kitesurfing',
					'wind surfing'
				],
				'landmark' => [
					'landmark',
					'memorial',
					'monument',
					'statue'
				],
				'live-music' => [
					'// Sometimes a category',
					'band',
					'concert',
					'live band',
					'performance',
					'sometimes a tag                    live music'
				],
				'lodging' => [
					'bnb',
					'guesthouse',
					'inn',
					'lodging'
				],
				'maasai-market' => [
					'beads',
					'curio',
					'handicrafts',
					'maasai market',
					'masai market'
				],
				'massage' => [
					'body rub',
					'massage',
					'masseuse'
				],
				'maze' => [
					'corn maze',
					'hedge maze',
					'maize maze',
					'maze'
				],
				'mini-golf' => [
					'crazy golf',
					'mini golf',
					'putt putt'
				],
				'mosque' => [
					'masjid',
					'mosque'
				],
				'museum' => [
					'gallery',
					'heritage',
					'historical site',
					'history',
					'museum'
				],
				'nature-walk' => [
					'bush walk',
					'forest walk',
					'nature trail',
					'nature walk',
					'nature walks',
					'walking trail',
					'jungle',
					'trees',
					'woods'
				],
				'night-club' => [
					'// Merged with nightlife concepts                    club',
					'club',
					'dance club',
					'dancing',
					'dj',
					'nightclub',
					'party'
				],
				'nyama-choma-zone' => [
					'bbq',
					'choma',
					'goat meat',
					'mbuzi',
					'nyama choma',
					'roast meat',
					'nyama choma zone'
				],
				'ostrich-riding' => [
					'ostrich',
					'ostrich ride',
					'ostrich riding'
				],
				'paddleboarding' => [
					'paddleboard',
					'stand up paddle',
					'sup'
				],
				'paintball' => [
					'combat game',
					'paintball',
					'paintball arena',
					'shooting game',
					'team combat'
				],
				'park' => [
					'gardens',
					'green space',
					'park',
					'public park',
					'recreational park'
				],
				'picnic-site' => [
					'basket',
					'carry food',
					'grass',
					'outdoor picnic',
					'picnic'
				],
				'place-of-worship' => [
					'place of worship',
					'spiritual center'
				],
				'pool-table' => [
					'billiards',
					'cue sports',
					'pool table',
                    'pool tables',
					'snooker'
				],
				'pottery' => [
					'ceramic',
					'clay',
					'moulding',
					'pottery',
					'pottery workshop',
					'pottery class'
				],
				'quad-biking' => [
                    'quad bike', 'atv', 'four wheel', 'offroad biking', 
                    'quad biking' // Added exact phrase
                ],
				'religious-retreat' => [
					'christian retreat',
					'church camp',
					'faith retreat',
					'prayer camp',
					'religious retreat',
					'spiritual',
					'spiritual getaway'
				],
				'resort' => [
					'holiday resort',
					'luxury stay',
					'resort',
					'vacation'
				],
				'restaurant' => [
					'dining',
					'dinner',
					'eatery',
					'fine dining',
					'lunch',
					'restaurant'
				],
				'rock-climbing' => [
					'bouldering',
					'cliff climbing',
					'indoor climbing',
					'outdoor climbing',
					'rock climbing'
				],
				'roller-coaster' => [
					'roller coaster',
					'thrill rides'
				],
				'roller-skating' => [
					'roller skating',
					'skate rink'
				],
				'rooftop-bar' => [
					'rooftop bar',
					'rooftop drinks',
					'rooftop view',
					'sky bar'
				],
				'safari-camp' => [
					'bush camp',
					'glamping',
					'safari camp',
					'tented camp'
				],
				'salon' => [
					'beauty parlor',
					'hair cut',
					'hairdresser',
					'salon'
				],
				'salon-barber' => [
					'barber',
					'grooming',
					'haircut',
					'salon'
				],
				'school' => [
					'academy',
					'education',
					'school'
				],
				'scuba-diving' => [
					'deep sea',
					'diving',
					'marine diving',
					'scuba diving',
					'underwater'
				],
				'shopping-mall' => [
					'mall',
					'retail complex',
					'shopping centre'
				],
				'skating' => [
					'roller skating',
					'skate park',
					'skating'
				],
				'snake-park' => [
					'reptile park',
					'reptiles',
					'snake park',
					'snakes'
				],
				'snorkeling' => [
					'coral reef',
					'reef snorkeling',
					'snorkel',
					'snorkeling'
				],
				'soccer' => [
					'football',
					'match',
					'pitch',
					'soccer'
				],
				'spa' => [
					'massage',
					'relaxation',
					'sauna',
					'spa',
					'steam room',
					'wellness'
				],
				'staycation' => [
					'local vacation',
					'staycation',
					'weekend getaway'
				],
				'supermarket' => [
					'grocery store',
					'retail',
					'shopping centre',
					'supermarket'
				],
				'surfing' => [
					'surf board',
					'surfing',
					'waves'
				],
				'swimming' => [
                    'swimming pool', // Primary
                    'swimming',      // Safe
                    'lap pool',
                    'pool',
                    'kids pool',
                    'baby pool',
                    'heated pool',
                    'plunge pool',
                    'dip in the water',
                    'poolside',      // Usually implies swimming
                    'infinity pool'
                ],
				'tea-farm-tour' => [
					'limuru tea',
					'tea estate',
					'tea farm',
					'tea picking',
					'tea plantation',
					'tea tour',
					'coffee estate',
					'coffee farm',
					'coffee picking',
					'coffee plantation',
					'coffee tour',
					'tea farm tour',
					'coffee farm tour',
				],
				'team-building' => [
					'bonding',
					'company retreat',
					'corporate bonding',
					'corporate event',
					'group activities',
					'staff outing',
					'team building',
					'team retreat',
					'team-building'
				],
				'tennis' => [
					'tennis',
					'tennis court'
				],
				'trampoline-park' => [
					'jump arena',
					'jump park',
					'trampoline'
				],
				'travel-agency' => [
					'booking agent',
					'tour operator',
					'travel agency'
				],
				'university' => [
					'campus',
					'college',
					'university'
				],
				'veterinary-care' => [
					'animal doctor',
					'vet',
					'veterinary'
				],
				'villa' => [
					'holiday home',
					'private home',
					'villa'
				],
				'virtual-reality' => [
					'immersive gaming',
					'oculus',
					'simulation',
					'virtual reality',
					'vr',
					'driving simulator',
					'f1',
					'f1 simulator',
					'motorsport sim',
					'racing rig',
					'sim racing',
					'simulator'
				],
				'water-rafting' => [
					'rafting',
					'rapids',
					'white water',
					'white water rafting'
				],
				'water-sports' => [
					'boat ride',
					'jet ski',
					'kayaking',
					'water skiing',
					'water sports'
				],
				'waterfall' => [
					'beautiful falls',
					'cascades',
					'chasing waterfalls',
					'rapids',
					'river falls',
					'scenic falls',
					'the falls',
					'twin falls',
					'waterfall',
					'waterfalls'
				],
				'wedding-venue' => [
					'bridal shower',
					'destination wedding',
					'garden wedding',
					'marriage ceremony',
					'vows',
					'wedding',
					'wedding reception',
					'wedding venue',
					'weddings'
				],
				'wine-tasting' => [
					'red wine',
					'vineyard',
					'white wine',
					'wine',
					'wine list',
					'wine pairing',
					'wine selection',
					'wine tasting',
					'wine tour'
				],
				'shooting-range' => [
					'firing range',
					'target practice',
					'gun range',
					'practice range',
					'shooting arena',
					'aiming range',
					'sports shooting',
					'shooting range',
					'shooting'
				],
				'meditation' => [
					'meditation',
					'mindfulness',
					'yoga',
					'zen',
					'relaxation',
					'contemplation',
					'calmness',
					'self-reflection',
					'inner peace',
					'spiritual practice',
					'breathing exercises'
				],
				'bonfire' => [
					'bonfire',
					'campfire',
					'fire pit',
					'outdoor fire',
					'evening fire',
					'campfire gathering',
					'fireside',
					'open fire'
				],
				'hidden-gems' => [
					'absolute gem',
					'discovery',
					'hidden gem',
					'hidden gems',
					'secret',
					'secret spot',
					'true gem',
					'underrated'
				],
				'tourist-attraction' => [
					'tourist attraction',
					'landmark',
					'sightseeing spot',
					'point of interest',
					'must-see',
					'travel destination',
					'famous site',
					'tourist site',
					'heritage site',
					'popular spot',
					'monument'
				],
				'saunas' => [
					'sauna',
					'steam bath',
					'hot room',
					'wellness',
					'spa',
					'relaxation',
					'heat therapy',
					'detox',
					'hot sauna',
					'thermal spa'
				],
				'paragliding' => [
					'paragliding',
					'parachuting',
					'aerial adventure',
					'air sports',
					'gliding',
					'hang gliding',
					'soaring',
					'sky adventure',
					'flying experience',
					'parachute'
				],
				'darts' => [
					'darts',
					'dartboard',
				],

				'yoga' => [
					'meditation',
					'pilates',
					'wellness class',
					'yoga'
				],
				'ziplining' => [
                        'zipline', 'zip lining', 'canopy', 'canopy tour', 
                        'flying fox', 'zip lines','ziplining'
                    ],
				'zoo' => [
					'animal park',
					'wildlife park',
					'zoo'
				],
			],
			'facilities' => [
				'accessible-trailspaths' => [
					'accessible trails',
					'flat paths',
					'paved paths'
				],
				'air-conditioning' => [
					'ac',
					'air conditioning',
					'cool rooms'
				],
				'airstrip-nearby' => [
					'airstrip nearby',
					'private airstrip'
				],
				'ample-parking' => [
					'ample parking',
					'lots of parking',
					'parking space',
					'plenty parking',
					'secure parking'
				],
				'baby-changing-station' => [
					'baby changing',
					'nursing room'
				],
				'backup-generator' => [
					'blackout',
					'generator',
					'power backup',
					'stima'
				],
				'barlounge-area' => [
					'bar area',
					'cocktail bar',
					'drinks area',
					'lounge'
				],
				'bathtub' => [
					'bath tub',
					'bathtub',
					'soaking tub'
				],
				'bike-racks' => [
					'bicycle parking',
					'bike racks',
					'bike stand'
				],
				'bonfire-fireplace' => [
					'bonfire',
					'chimney',
					'fire pit',
					'fireplace',
					'log fire'
				],
				'bouncing-castle' => [
					'bouncing castle',
					'bouncy castle'
				],
				'buffet' => [
					'breakfast buffet',
					'buffet',
					'dinner buffet'
				],

				'byof-bring-your-own-food' => [
					'bring your own food',
					'byof',
					'carry your own food',
					'picnic allowed',
					'bring your own bottle',
					'byob',
					'corkage fee',
					'picnic area',
					'picnic benches',
					'picnic site'
				],
				'cctv-security-systems' => [
					'cctv',
					'security cameras',
					'surveillance'
				],
				'charging-stations' => [
					'charging points',
					'charging stations',
					'plug points',
					'power sockets'
				],
				'child-friendly' => [
					'// Used as a facility                    child friendly',
					'baby friendly',
					'good for kids',
					'kid friendly'
				],
				'clean-restrooms' => [
					'clean toilet',
					'clean washroom',
					'hygienic'
				],
				'conference-hall' => [
					'boardroom',
					'conference hall',
					'meeting room'
				],
				'conference-rooms' => [
					'boardroom',
					'conference rooms',
					'meeting rooms'
				],
				'digital-payment-accepted' => [
					'card payment',
					'cashless',
					'credit card',
					'digital payment',
					'm-pesa',
					'mpesa'
				],
				'eco-friendly' => [
					'eco friendly',
					'green energy',
					'solar',
					'sustainable'
				],
				'electric-fence' => [
					'electric fence',
					'secure fence'
				],
				'fireplace' => [
					'chimney',
					'fireplace',
					'log fire',
					'wood fire'
				],
				'free-wi-fi' => [
					'free wifi',
					'good connection',
					'internet',
					'wifi',
					'wi-fi',
					'wireless internet'
				],
				'free-wifi' => [
					'free wifi',
					'good connection',
					'internet',
					'wifi',
					'wi-fi',
					'wireless internet'
				],
				'garden' => [
					'garden',
					'green space',
					'lawn'
				],
				'garden-lawns' => [
					'garden',
					'green grass',
					'lawn',
					'manicured grounds'
				],
				'gazebos' => [
					'bandas',
					'gazebos',
					'private hut',
					'shade'
				],
				'gift-shop' => [
					'curio shop',
					'gift shop',
					'souvenir shop'
				],
				'halal-options' => [
					'halal',
					'halal certified',
					'halal food'
				],
				'heated-pool' => [
					'heated pool',
					'temperature controlled',
					'warm pool',
					'warm water'
				],
				'helipad' => [
					'helicopter landing',
					'helipad'
				],
				'hot-shower' => [
					'cold shower',
					'hot shower',
					'hot water',
					'shower',
					'warm bath',
					'warm water'
				],
				'hot-water-bottles' => [
					'bush baby',
					'hot water bottles',
					'water bottle'
				],
				'in-house-restaurantcafe' => [
					'cafe on site',
					'hotel restaurant',
					'restaurant on site'
				],
				'indoor-dining' => [
					'dining hall',
					'indoor dining',
					'inside seating'
				],
				'kids-play-area' => [
					'kids play',
					'play area',
					'playground',
					'swings'
				],
				'kids-pool' => [
					'baby pool',
					'kids pool',
					'wading pool'
				],
				'kitchenette' => [
					'cook your own',
					'kitchenette',
					'self catering'
				],
				'kosher-options' => [
					'kosher'
				],
				'live-band' => [
					'live band',
					'live music',
					'karaoke'
				],
				'live-entertainment' => [
					'live entertainment',
					'performers',
					'show'
				],
				'lockersstorage-area' => [
					'cloakroom',
					'lockers',
					'luggage storage',
					'storage'
				],
				'm-pesa-accepted' => [
					'lipa na mpesa',
					'm-pesa',
					'mpesa',
					'till'
				],
				'mosquito-nets' => [
					'mosquito net',
					'mosquitoes',
					'nets'
				],
				'online-booking-available' => [
					'book online',
					'online booking',
					'website booking'
				],
				'outdoor-seating' => [
					'al fresco',
					'garden seating',
					'outdoor seating',
					'outside tables',
					'terrace'
				],
				'parking-available' => [
					'ample parking',
					'car park',
					'parking available',
					'parking lot',
					'secure parking'
				],
				'pet-friendly' => [
					'cats allowed',
					'dogs allowed',
					'pet friendly',
					'pets welcome'
				],
				'picnic-area' => [
					'picnic area',
					'picnic benches',
					'picnic site'
				],
				'playground' => [
					'play area',
					'playground',
					'swings',
					'play area',
					'kids park',
					'children’s park',
					'jungle gym',
					'swing set',
					'slides',
					'climbing frame',
					'recreation area',
					'fun zone',
					'adventure park',
					'activity area',
					'outdoor play space'
				],
				'pool-table' => [
                    'pool table',    // Primary
                    'game of pool',
                    'play pool',
                    'cue',
                    'billiards',
                    'snooker',
                    'cue sports',
                    'pool table area'
                ],
				'prayer-room' => [
					'mosque',
					'place to pray',
					'prayer room',
					'swallah'
				],
				'private-event-spaces' => [
					'function room',
					'private event space',
					'private hall'
				],
				'private-plunge-pool' => [
					'private plunge pool',
					'private pool'
				],
				'quietrelaxation-zone' => [
					'quiet zone',
					'relaxation area',
					'silent room'
				],
				'rooftop-seating' => [
					'rooftop seating',
					'rooftop view',
					'upper deck'
				],
				'secure-parking' => [
					'guarded parking',
					'secure parking'
				],
				'service-animals-allowed' => [
					'guide dogs',
					'service animals'
				],
				'showers' => [
					'changing rooms',
					'hot shower',
					'showers'
				],
				'smoking-area' => [
					'designated smoking',
					'smoking area',
					'smoking zone'
				],
				'solar-power' => [
					'off grid',
					'solar energy',
					'solar power'
				],
				'souvenir-store' => [
					'memorabilia',
					'souvenir store'
				],
				'sports-screens' => [
					'big screen',
					'football',
					'premier league',
					'watched the match'
				],
				'stroller-accessible' => [
					'buggy friendly',
					'pram friendly',
					'stroller accessible'
				],
				'booking-friendly' => [
					'booking friendly',
					'easy to book',
					'reservation available',
					'bookable',
					'pre-booking',
					'advance booking',
					'reservation friendly',
					'online booking',
					'easy reservations'
				],
				'family-friendly' => [
					'family friendly',
					'kid friendly',
					'child friendly',
					'suitable for families',
					'family oriented',
					'all ages',
					'family approved',
					'family activities',
					'family appropriate'
				],
				'swimming-pool' => [
					'kids pool',
					'pool area',
					'pool clean',
					'swimming pool'
				],
				'tent-camping-space' => [
					'camping grounds',
					'pitch a tent',
					'tent space'
				],
				'trampoline' => [
					'jumping mat',
					'trampoline'
				],
				'vegan-options' => [
					'plant based',
					'vegan'
				],
				'vegetarian-options' => [
					'vegetarian',
					'veggie options'
				],
				'vr-experiences' => [
					'oculus',
					'virtual reality',
					'vr experience'
				],
				'wheelchair-accessible' => [
					'accessibility',
					'disabled access',
					'ramp',
					'step free',
					'wheelchair accessible'
				],
				'wheelchair-ramp' => [
					'disabled access',
					'wheelchair access',
					'wheelchair ramp'
				],
			],
			'tags' => [
				'4x4-required' => [
					'4x4',
					'bad road',
					'offroad',
					'rough road'
				],
				'alcohol-free' => [
					'alcohol free',
					'halal',
					'no alcohol'
				],
				'breakfast' => [
					'breakfast',
					'english breakfast',
					'morning meal'
				],
				'brunch' => [
					'brunch',
					'late breakfast',
					'sunday brunch'
				],
				'budget-friendly' => [
					'affordable',
					'budget friendly',
					'cheap',
					'good value',
					'pocket friendly',
					'reasonable prices'
				],
				'buffet-available' => [
					'all you can eat',
					'buffet'
				],
				'business-meetings' => [
					'business meetings',
					'corporate',
					'formal'
				],
				'casual' => [
					'casual',
					'informal',
					'laid back',
					'relaxed'
				],
				'cocktails' => [
					'cocktails',
					'drinks',
					'mixology'
				],
				'coffee-tea' => [
					'chai',
					'coffee',
					'masala tea',
					'tea'
				],
				'craft-beer' => [
					'craft beer',
					'draft beer',
					'microbrewery'
				],
				'cultural-attraction' => [
					'cultural attraction',
					'heritage',
					'history'
				],
				'delivery-available' => [
					'delivery',
					'glovo',
					'home delivery',
					'uber eats'
				],
				'desserts' => [
					'cake',
					'desserts',
					'ice cream',
					'sweet treats'
				],
				'dinner' => [
					'dinner',
					'evening meal',
					'supper'
				],
				'dj-nights' => [
					'dj',
					'live mix',
					'party music'
				],
				'family-friendly' => [
					'family friendly',
					'good for kids',
					'kids and adults',
					'whole family'
				],
				'fast-service' => [
					'efficient',
					'fast service',
					'quick service'
				],
				'fine-dining' => [
					'expensive',
					'fine dining',
					'gourmet',
					'luxury',
					'upscale'
				],
				'free-wifi' => [
					'fast internet',
					'free wifi'
				],
				'gluten-free-options' => [
					'gf options',
					'gluten free'
				],
				'good-for-groups' => [
					'chama',
					'good for groups',
					'group of friends',
					'large group',
					'large groups',
					'team'
				],
				'good-for-kids' => [
					'family friendly',
					'good for kids',
					'high chairs',
					'kids menu'
				],
				'halal' => [
					'halal',
					'halal certified'
				],
				'outdoor-seating' => [
					'outdoor seating',
					'open-air seating',
					'patio seating',
					'terrace seating',
					'garden seating',
					'al fresco',
					'outside dining',
					'deck seating',
					'veranda seating',
					'open seating'
				],
				'rooftop' => [
					'rooftop',
					'rooftop bar',
					'terrace',
					'sky lounge',
					'roof deck',
					'high terrace',
					'panoramic terrace',
					'roof garden',
					'top floor',
					'open-air rooftop'
				],
				'halal-options' => [
					'halal food',
					'halal options'
				],
				'happy-hour' => [
					'cocktail hour',
					'discounted drinks',
					'drinks offer',
					'happy hour'
				],
				'hidden-gems' => [
					'absolute gem',
					'discovery',
					'hidden gem',
					'hidden gems',
					'secret',
					'secret spot',
					'true gem',
					'underrated'
				],
				'hiking-nearby' => [
					'hiking nearby',
					'trails nearby'
				],
				'historic' => [
					'colonial',
					'historic',
					'old school',
					'vintage'
				],
				'international-cuisine' => [
					'continental',
					'global menu',
					'international cuisine'
				],
				'live-music' => [
					'acoustic',
					'band',
					'live music'
				],
				'lively-trendy' => [
					'buzzing',
					'happening',
					'lively',
					'trendy',
					'upbeat',
					'vibrant'
				],
				'local-cuisine' => [
					'african dishes',
					'kenyan food',
					'local cuisine',
					'traditional food'
				],
				'lunch' => [
					'lunch',
					'midday meal'
				],
				'luxury' => [
					'high end',
					'luxurious',
					'luxury',
					'premium'
				],
				'modern' => [
					'chic',
					'contemporary',
					'modern',
					'stylish'
				],
				'nightlife-hotspot' => [
					'clubbing',
					'nightlife',
					'party spot'
				],
				'nyama-choma' => [
					'barbecue',
					'choma',
					'mbuzi',
					'nyama'
				],
				'parking-available' => [
					'parking',
					'secure parking'
				],
				'pet-friendly' => [
					'animal friendly',
					'cat',
					'dog',
					'pet'
				],
				'pocket-friendly' => [
					'affordable',
					'budget',
					'cheap',
					'pocket friendly',
					'reasonable prices',
					'value for money'
				],
				'quiet-atmosphere' => [
					'calm',
					'peaceful',
					'quiet',
					'serene',
					'tranquil'
				],
				'reservations' => [
					'book a table',
					'booking advised',
					'reservations'
				],
				'romantic' => [
					'couples',
					'cozy',
					'date night',
					'honeymoon',
					'intimate',
					'romantic'
				],
				'rustic' => [
					'countryside',
					'rustic',
					'traditional',
					'village style'
				],
				'scenic-view' => [
					'amazing view',
					'instagram',
					'landscape',
					'panoramic',
					'scenery',
					'scenic view',
					'stunning views',
					'sunset',
					'view'
				],
				'seafood' => [
					'crab',
					'fish',
					'lobster',
					'prawns',
					'seafood'
				],
				'serene-environment' => [
					'calm',
					'peaceful',
					'quiet',
					'serene',
					'tranquil'
				],
				'shopping-nearby' => [
					'near mall',
					'near market',
					'shopping nearby'
				],
				'solo-traveller-friendly' => [
					'alone',
					'alone trip',
					'solo travel',
					'solo traveller'
				],
				'takeaway-available' => [
					'carry out',
					'takeaway',
					'takeout',
					'to go'
				],
				'trendy-vibe' => [
					'aesthetic',
					'modern',
					'stylish',
					'trendy',
					'vibe'
				],
				'vegan-options' => [
					'plant based',
					'vegan options'
				],
				'vegetarian-friendly' => [
					'veg options',
					'vegetarian friendly'
				],
				'vegetarian-options' => [
					'plant based',
					'vegetarian',
					'veggie'
				],
				'wheelchair-accessible' => [
					'ramp access',
					'wheelchair'
				],
				'wine-tasting' => [
					'red wine',
					'white wine',
					'wine',
					'wine list',
					'wine selection',
					'wine tasting'
				],
				'work-friendly' => [
					'good wifi',
					'laptop',
					'sockets',
					'work from cafe'
				],
			],
		];
    }


    protected function getCombinedMappings(): array
    {
        $mappings = $this->getMappings(); 

        foreach ($this->allCategories as $slug => $category) {
            $this->mergeDynamicKeyword($mappings['categories'], $slug, $category->name);
        }
        foreach ($this->allFacilities as $slug => $facility) {
            $this->mergeDynamicKeyword($mappings['facilities'], $slug, $facility->name);
        }
        foreach ($this->allTags as $slug => $tag) {
            $this->mergeDynamicKeyword($mappings['tags'], $slug, $tag->name);
        }

        return $mappings;
    }

    private function mergeDynamicKeyword(array &$targetGroup, string $slug, string $name): void
    {
        $keyword = strtolower($name);
        if (!isset($targetGroup[$slug])) {
            $targetGroup[$slug] = [];
        }
        if (!in_array($keyword, $targetGroup[$slug])) {
            $targetGroup[$slug][] = $keyword;
        }
    }

    private function findMatches(string $text, array $mapping, Collection $dbRecords, string $typeLabel, string $businessName): array
    {
        $foundIds = [];

        foreach ($mapping as $slug => $keywords) {
            if (!isset($dbRecords[$slug])) continue;

            foreach ($keywords as $k) {
                // 1. Generate all linguistic variants (swim, swims, swimming, swimmer)
                $variants = $this->getVariants($k);

                // 2. Build a combined Regex pattern
                // Pattern example for 'hike': /\b(hike|hikes|hiking|hiker|hikers)\b/i
                $quotedVariants = $variants->map(fn($v) => preg_quote($v, '/'))->implode('|');
                $pattern = '/\b(' . $quotedVariants . ')\b/i';

                if (preg_match($pattern, $text, $matches)) {
                    $foundIds[] = $dbRecords[$slug]->id;
                    Log::info("MATCH: [{$businessName}] found '{$matches[0]}' -> Added {$typeLabel}: '{$slug}'");
                    break; 
                }
            }
        }

        return $foundIds;
    }

    /**
     * Logic to generate word variants (Stemming).
     * Prevents over-matching on short words while expanding longer ones.
     */
    /**
     * Smart Variant Generator
     * Automatically handles:
     * 1. Plurals (hike <-> hikes)
     * 2. Verb endings (hike -> hiking, hiked, hiker)
     * 3. Separators (sky biking <-> sky-biking)
     */
    private function getVariants(string $word): Collection
    {
        $word = strtolower(trim($word));
        $variants = collect([$word]); 

        // 1. Handle Hyphens vs Spaces (e.g., "sky-biking" matches "sky biking")
        if (str_contains($word, ' ')) {
            $variants->push(str_replace(' ', '-', $word));
        } elseif (str_contains($word, '-')) {
            $variants->push(str_replace('-', ' ', $word));
        }

        // 2. Add Singular/Plural Base Forms
        $variants->push(Str::singular($word));
        $variants->push(Str::plural($word));

        // 3. Handle Verb Endings (ing, ed, er)
        // We look at the LAST word in the phrase (e.g., "quad bike" -> we modify "bike")
        $parts = preg_split('/[\s-]+/', $word); // Split by space or hyphen
        $lastWord = end($parts);
        
        // Only do stemming on words longer than 3 chars to avoid noise
        if (strlen($lastWord) > 3) {
            $stems = [];
            
            // Logic for words ending in 'e' (hike -> hiking) vs normal (camp -> camping)
            if (str_ends_with($lastWord, 'e')) {
                $root = substr($lastWord, 0, -1); // Remove 'e'
                $stems[] = $root . 'ing'; // hiking
                $stems[] = $root . 'ed';  // hiked
                $stems[] = $root . 'er';  // hiker
            } else {
                $stems[] = $lastWord . 'ing'; // camping
                $stems[] = $lastWord . 'ed';  // camped
                $stems[] = $lastWord . 'er';  // camper
            }

            // If it was a phrase (e.g., "quad bike"), reconstruct it with the new stem
            if (count($parts) > 1) {
                // Get the prefix (everything before the last word)
                // Note: precise reconstruction of separators (space/hyphen) is tricky, 
                // so we default to the separator found in the original string or space.
                $separator = str_contains($word, '-') ? '-' : ' ';
                $prefix = implode($separator, array_slice($parts, 0, -1));
                
                foreach ($stems as $stem) {
                    $variants->push($prefix . $separator . $stem);
                }
            } else {
                // It was just a single word
                foreach ($stems as $stem) {
                    $variants->push($stem);
                }
            }
        }

        return $variants->unique()->filter();
    }
}