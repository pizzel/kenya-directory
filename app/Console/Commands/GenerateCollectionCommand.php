<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\County;
use App\Models\DiscoveryCollection;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class GenerateCollectionCommand extends Command
{
    /**
     * Usage: php artisan collection:generate "Beautiful Wedding Grounds in Nairobi"
     */
    protected $signature = 'collection:generate {query} {--limit=26}';

    protected $description = 'Creates a premium curated collection by matching human phrases to database taxonomy.';

    public function handle(): int
    {
        $inputQuery = $this->argument('query');
        $this->info("--------------------------------------------------");
        $this->info("🤖 PROCESSING REQUEST: \"{$inputQuery}\"");

        // 1. CLEAN INPUT
        $cleanQuery = strtolower(preg_replace('/[^\w\s-]/', '', $inputQuery));
        
        // 2. DETECT COUNTY (And remove it from query to prevent it being matched as an activity)
        $detectedCounty = null;
        $allCounties = \App\Models\County::all();
        foreach ($allCounties as $county) {
            $cName = strtolower($county->name);
            if (preg_match("/\b" . preg_quote($cName, '/') . "\b/i", $cleanQuery)) {
                $detectedCounty = $county;
                $cleanQuery = preg_replace("/\b" . preg_quote($cName, '/') . "\b/i", '', $cleanQuery);
                $this->info("📍 STEP 1: Identified County -> [{$county->name}]");
                break;
            }
        }
        if (!$detectedCounty) $this->info("📍 STEP 1: No specific county found. Searching Nationwide.");

        // 3. PREPARE THE DICTIONARY (Names + Synonyms)
        $synonymMap = [
            'wedding-venue'    => ['wedding', 'marriage', 'vows', 'wedding reception', 'aisle', 'bridal', 'grounds', 'garden wedding'],
            'go-karting'       => ['karting', 'go kart', 'gokart', 'racing', 'track', 'circuit'],
            'kids-park'        => ['kids play', 'play area', 'funland', 'bouncing castle', 'playground'],
            'nyama-choma-zone' => ['nyama choma', 'mbuzi', 'roast meat', 'meat joint'],
            'hiking'           => ['hike', 'trekking', 'trail', 'climb'],
            'staycation'       => ['staycation', 'getaway', 'weekend break'],
            'conference-centre'=> ['meeting hall', 'conference', 'seminar', 'boardroom'],
            'rooftop-bar'      => ['rooftop', 'sky bar', 'view of the city'],
            'picnic-site'      => ['picnic', 'outdoor lunch', 'garden picnic'],
            'spa'              => ['wellness', 'massage', 'therapy'],
            'pocket-friendly'  => ['affordable', 'cheap', 'budget', 'value for money'],
            'hidden-gem'       => ['hidden gem', 'underrated', 'secret spot'],
			'team-building'    => ['team building', 'corporate event', 'group activities', 'bonding'],
			'game-drive'       => ['game drive', 'safari', 'animals', 'lions', 'game viewing', 'big five'],
			'golfing'          => ['golf course', 'golf club', '18 holes', 'caddy'],
			'boat-riding'      => ['boat ride', 'boat trip', 'lake ride', 'canoe'],
			'kids-park'        => ['kids park', 'kids play', 'play area', 'funland', 'bouncing castle', 'slides'],
			'nyama-choma-zone' => ['nyama choma', 'mbuzi', 'choma', 'meat', 'butchery'],
			'wedding-venue'    => ['wedding venue', 'wedding' ,'wedding reception', 'marriage ceremony', 'vows', 'bridal shower','weddings'],
			'hospital'         => ['medical center', 'clinic', 'emergency room', 'doctors office'],
			'scuba-diving'     => ['scuba diving', 'diving', 'underwater', 'deep sea'],
			'maze'             => ['maize maze', 'hedge maze'],
			'kitesurfing'      => ['kite', 'kitesurfing', 'kite surfing', 'kite school'],
			'glass-bottom-boat'=> ['glass bottom', 'glass boat', 'view the fish'],
			'tea-farm-tour'    => ['tea farm', 'tea picking', 'tea tour', 'limuru tea'],
			'pottery-class'    => ['pottery', 'clay', 'ceramic', 'moulding'],
			'paint-sip'        => ['paint and sip', 'painting', 'art class'],
			'camel-riding'     => ['camel', 'camel ride'],
			'escape-room'      => ['escape room', 'puzzle', 'locked'],
			'virtual-reality'  => ['vr', 'virtual reality', 'oculus', 'simulation'],
			'sim-racing'       => ['sim racing', 'simulator', 'f1', 'racing rig'],
			'maasai-market'    => ['maasai market', 'masai market', 'curio', 'beads'],
			'picnic-site'      => ['picnic', 'carry food', 'grass', 'basket'],
			'staycation'       => ['staycation', 'weekend getaway', 'vacation'],
			'glamping'         => ['glamping', 'luxury tent', 'eco camp'],
			'rooftop-bar'      => ['rooftop', 'view of the city', 'sky bar', 'upper deck'],
			'water-rafting'    => ['rafting', 'white water', 'rapids'],
			'hiking-trekking'  => ['hike', 'trail', 'climb', 'walk in the forest'],
			'snorkeling'       => ['snorkeling', 'snorkel', 'coral reef'],
			'dhow-cruise'      => ['dhow cruise', 'sunset cruise', 'swahili boat'],
			'jet-skiing'       => ['jet ski', 'jetski', 'water bike'],
			'paddleboarding'   => ['paddleboard', 'stand up paddle', 'sup'],
			'go-karting'       => ['go kart', 'karting', 'race track'],
			'quad-biking'      => ['quad bike', 'atv', 'four wheel'],
			'ziplining'        => ['zipline', 'zip lining', 'canopy'],
			'paintball'        => ['paintball', 'combat game', 'shooting game'],
			'archery'          => ['archery', 'bow and arrow', 'target shooting'],
			'high-ropes'       => ['high ropes', 'rope course', 'aerial course'],
			'rock-climbing'    => ['rock climbing', 'cliff climbing', 'bouldering'],
			'ice-skating'      => ['ice skating', 'ice rink'],
			'roller-skating'   => ['roller skating', 'skate rink'],
			'trampoline-park'  => ['trampoline', 'jump park'],
			'bowling-alley'    => ['bowling', 'bowling alley', 'pins'],
			'indoor-play-area' => ['indoor play', 'soft play', 'kids indoor'],
			'amusement-park'   => ['amusement park', 'theme park', 'rides'],
			'aquapark'         => ['water park', 'slides', 'wave pool'],
			'coffee-farm-tour' => ['coffee farm', 'coffee tour', 'coffee picking'],
			'cheese-tasting'   => ['cheese tasting', 'dairy tour'],
			'horse-riding'     => ['horse riding', 'horseback'],
			'ostrich-riding'   => ['ostrich', 'ostrich ride'],
			'snake-park'       => ['snake park', 'reptile park'],
			'bird-watching'    => ['bird watching', 'birding'],
			'camping'          => ['camping', 'camp site', 'tents'],
			'conservancy'      => ['conservancy', 'protected area'],
			'museum'           => ['museum', 'history', 'heritage'],
			'art-gallery'      => ['art gallery', 'exhibition'],
			'cultural-centre'  => ['cultural centre', 'culture', 'traditions'],
			'cinema'           => ['cinema', 'movie theatre'],
			'conference-centre'=> ['conference', 'meeting hall', 'seminar'],
			'religious-retreat'=> ['religious retreat', 'church camp', 'spiritual'],
			'villa'            => ['villa', 'private home'],
			'airbnb'           => ['airbnb', 'short stay'],
			'hotel'            => ['hotel', 'lodging'],
			'hostel'           => ['hostel', 'backpackers'],
			'resort'           => ['resort', 'holiday resort'],
			'safari-camp'      => ['safari camp', 'tented camp'],
			'coffee-shop'      => ['coffee shop', 'cafe'],
			'restaurant'       => ['restaurant', 'dining'],
			'night-club'       => ['club', 'nightclub', 'party'],
			'bar-lounge'       => ['bar', 'lounge', 'cocktails'],
			'spa'              => ['spa', 'massage', 'wellness'],
			'gym'              => ['gym', 'fitness', 'workout'],
			'shopping-mall'    => ['mall', 'shopping centre'],
        ];

        $searchableItems = collect();
        $taxonomies = [
            'category' => \App\Models\Category::all(),
            'tag'      => \App\Models\Tag::all(),
            'facility' => \App\Models\Facility::all()
        ];

        foreach ($taxonomies as $type => $items) {
            foreach ($items as $item) {
                // Add the official name
                $searchableItems->push(['id' => $item->id, 'phrase' => strtolower($item->name), 'type' => $type, 'name' => $item->name]);
                // Add synonyms
                if (isset($synonymMap[$item->slug])) {
                    foreach ($synonymMap[$item->slug] as $syn) {
                        $searchableItems->push(['id' => $item->id, 'phrase' => strtolower($syn), 'type' => $type, 'name' => $item->name]);
                    }
                }
            }
        }

        // 4. THE "FIRST WORD WINS" RULE
        $this->info("🔍 STEP 2: Scanning sentence from start to finish for first match...");
        
        // Split the query into words while keeping the original order
        $words = explode(' ', $cleanQuery);
        $finalMatch = null;

        // We loop through the sentence, word by word
        for ($i = 0; $i < count($words); $i++) {
            
            // From this current word, we try to see if it or a phrase starting with it matches
            // We check up to 3 words ahead to catch phrases like "Nyama Choma"
            for ($length = 3; $length >= 1; $length--) {
                $phraseAttempt = implode(' ', array_slice($words, $i, $length));
                
                if (empty($phraseAttempt)) continue;

                // Check this phrase against our searchable items
                // We prioritize EXACT matches from the DB or synonyms
                $match = $searchableItems->filter(function($item) use ($phraseAttempt) {
                    $p = $item['phrase'];
                    return $p === $phraseAttempt || 
                           \Illuminate\Support\Str::plural($p) === $phraseAttempt || 
                           \Illuminate\Support\Str::singular($p) === $phraseAttempt;
                })->first();

                if ($match) {
                    $finalMatch = $match;
                    $this->info("✅ SUCCESS: Found [{$match['name']}] matching the phrase '{$phraseAttempt}' at the start of your query.");
                    $this->warn("⛔ STOPPING: Logic locked to your first signal. Ignoring the rest.");
                    break 2; // Exit both loops immediately
                }
            }
        }

        if (!$finalMatch) {
            $this->error("❌ FAIL: Could not find any recognized activity in your sentence.");
            return 1;
        }

        // 5. THE STRICT DATABASE QUERY (Using only the ONE locked ID)
         $this->info("📊 STEP 3: Querying database...");

        $relationMap = ['category' => 'categories', 'tag' => 'tags', 'facility' => 'facilities'];
        $relationName = $relationMap[$finalMatch['type']];

        // --- A. TRY THE SPECIFIC COUNTY FIRST ---
        $query = \App\Models\Business::where('status', 'active');
        if ($detectedCounty) { 
            $query->where('county_id', $detectedCounty->id); 
        }
        $query->whereHas($relationName, fn($q) => $q->where('id', $finalMatch['id']));

        $businesses = $query->with(['media', 'county'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('google_rating', 'desc')
            ->get();

        // --- B. THE "JACKPOT" BROADEN CHECK ---
        // If we found less than 5 results in the county, we ignore the county and search NATIONWIDE
        if ($detectedCounty && $businesses->count() < 5) {
            $this->warn("⚠️ Only found {$businesses->count()} in {$detectedCounty->name}. Broadening search to the whole of Kenya for better curation...");
            
            // Clear the detected county so the title and query both default to 'Kenya'
            $detectedCounty = null; 

            // Re-run query without county filter
            $businesses = \App\Models\Business::where('status', 'active')
                ->whereHas($relationName, fn($q) => $q->where('id', $finalMatch['id']))
                ->with(['media', 'county'])
                ->orderBy('is_featured', 'desc')
                ->orderBy('google_rating', 'desc')
                ->get();
        }

        // Shuffle after fetching to ensure randomization
        $businesses = $businesses->shuffle();

        $this->info("📈 FINAL RESULT: Found {$businesses->count()} businesses.");

        if ($businesses->isEmpty()) {
            $this->error("❌ FAIL: Database returned 0 results even after broadening.");
            return 1;
        }

        // 6. PUBLISH
        $curatedCount = ($businesses->count() >= 20) ? rand(18, 20) : $businesses->count();
        $finalList = $businesses->take($curatedCount);

        $currentYear = date('Y');
        
        // This label now correctly reflects 'Kenya' if the search was broadened
        $locationLabel = $detectedCounty ? $detectedCounty->name : 'Kenya';
        
        // SMART TITLE HUMANIZER 🤖
        $coreSubject = $finalMatch['name'];
        $coreSubjectLower = strtolower($coreSubject);

        // 1. Handle Uncountable Nouns & Specific Adjectives
        if (Str::endsWith($coreSubjectLower, 'ing') || in_array($coreSubjectLower, ['paintball', 'archery', 'golfing'])) {
            // "Go-Karting" -> "Go-Karting Venues"
            $subjectPhrase = "{$coreSubject} Venues"; 
            if ($coreSubjectLower === 'hiking') $subjectPhrase = "Hiking Trails";
            if ($coreSubjectLower === 'camping') $subjectPhrase = "Camping Spots";
        } elseif ($coreSubjectLower === 'pocket-friendly' || $coreSubjectLower === 'affordable') {
            $subjectPhrase = "Budget-Friendly Spots";
        } elseif ($coreSubjectLower === 'luxury' || $coreSubjectLower === 'hidden-gem') {
            $subjectPhrase = ($coreSubjectLower === 'luxury') ? "Luxury Experiences" : "Hidden Gems";
        } else {
            // "Hotel" -> "Hotels" (Standard plural)
            $subjectPhrase = Str::plural($coreSubject);
        }

        // 2. Formatting: "10 Best [Subject]" instead of "Top 10 Best [Subject]"
        $title = "{$curatedCount} Best {$subjectPhrase} in {$locationLabel} ({$currentYear} Guide)";

        $this->info("✨ Success! Generating: \"{$title}\"");

        $description = $this->generateCurationIntro($title, $finalList);
        $coverPath = null;
        if ($finalList->first() && $finalList->first()->media->isNotEmpty()) {
            $mediaItem = $finalList->first()->getFirstMedia('images');
            $coverPath = $mediaItem ? $mediaItem->id . '/' . $mediaItem->file_name : null;
        }

        $collection = \App\Models\DiscoveryCollection::updateOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($title)],
            [
                'title' => $title,
                'description' => $description,
                'is_active' => true,
                'display_order' => 0,
                'cover_image_url' => $coverPath
            ]
        );

        $collection->businesses()->sync($finalList->pluck('id'));

        \Illuminate\Support\Facades\Cache::forget('home_discovery_cards');
        \Illuminate\Support\Facades\Cache::forget('all_collections_for_search');

        $this->info("🔗 Published: /collections/{$collection->slug}");
        return 0;
    }

    private function generateCurationIntro(string $title, $businesses): string
    {
        $context = $businesses->take(3)->map(function($b) {
            return "- {$b->name}: " . $b->tags->pluck('name')->take(3)->join(', ');
        })->join("\n");

        $prompt = "Write a professional 2-paragraph travel guide introduction for a collection titled '{$title}'. Mention that our team analyzed hundreds of user reviews. Mention these highlights: \n{$context}. Tone: Inviting and expert.";

        if (env('OPENAI_API_KEY')) {
            try {
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);
                return $response->choices[0]->message->content;
            } catch (\Exception $e) { }
        }

        return "Explore the most highly-rated " . strtolower(Str::plural($title)) . " across the region. Our selection process involves checking verified traveler feedback and quality of service to ensure you have the best experience possible.";
    }
}