<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PublicBusinessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GeocodingController;
use App\Http\Controllers\BusinessOwner\DashboardController as BusinessOwnerDashboardController;
use App\Http\Controllers\BusinessOwner\BusinessController as BusinessOwnerBusinessController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BusinessOwner\EventController as BusinessOwnerEventController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\EventReviewController;
use App\Http\Controllers\DiscoveryCollectionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SocialiteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All routes in this file automatically use the 'web' middleware group.
*/

// --- PUBLIC ROUTES ---
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/benchmark', [App\Http\Controllers\BenchmarkController::class, 'index'])->name('benchmark.index');
Route::get('/ajax/search-suggestions', [App\Http\Controllers\HomeController::class, 'suggestions'])->name('ajax.search-suggestions');
Route::get('/ajax/businesses/search', [App\Http\Controllers\PublicBusinessController::class, 'search'])->name('ajax.businesses.search');
Route::get('/listings/search', [ListingController::class, 'search'])->name('listings.search');
Route::get('/listings/county/{countySlug}', [ListingController::class, 'countyListings'])->name('listings.county');
Route::get('/listings/category/{categorySlug}', [ListingController::class, 'categoryListings'])->name('listings.category');
Route::get('/listing/{businessSlug}', [PublicBusinessController::class, 'show'])->name('listings.show');
Route::get('/listing/{business}/weather', [PublicBusinessController::class, 'getWeather'])->name('listings.weather');
Route::get('/listings/facility/{facility:slug}', [ListingController::class, 'facilityListings'])->name('listings.facility');
Route::get('/listings/tag/{tag:slug}', [ListingController::class, 'tagListings'])->name('listings.tag');
Route::get('/listings', [ListingController::class, 'allListings'])->name('listings.index');
Route::get('/collections', [DiscoveryCollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{collection:slug}', [DiscoveryCollectionController::class, 'show'])->name('collections.show');
Route::get('/event/{eventSlug}', [PublicEventController::class, 'show'])->name('events.show.public');
Route::get('/events', [PublicEventController::class, 'indexPublic'])->name('events.index.public');
Route::get('/events/county/{countySlug}', [PublicEventController::class, 'eventsByCounty'])->name('events.by_county');
Route::get('/event/{eventSlug}/ics', [PublicEventController::class, 'downloadIcs'])->name('events.ics');
Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/listings/nearby', [ListingController::class, 'getNearbyListings'])->name('listings.nearby');
Route::get('/contact-us', [ContactController::class, 'show'])->name('contact.show');
Route::middleware('web')->get('/auth-status', fn () => response()->json(['loggedIn' => auth()->check(), 'userName' => auth()->check() ? auth()->user()->name : null]))->name('auth.status');
Route::post('/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/ajax/home-section', [App\Http\Controllers\HomeController::class, 'fetchHomeSection'])->name('ajax.home-section');
Route::get('/ajax/similar-listings/{businessSlug}', [PublicBusinessController::class, 'getSimilarListings'])->name('ajax.similar-listings');

// --- ITINERARY ROUTES ---
Route::get('/itineraries', [App\Http\Controllers\ItineraryController::class, 'index'])->name('itineraries.index');
Route::get('/itinerary/{itinerary:slug}', [App\Http\Controllers\ItineraryController::class, 'show'])->name('itineraries.show');


// --- FILAMENT REDIRECT (Old Admin) ---
Route::get('/admin', function() {
    return redirect()->route('admin.dashboard');
});

// --- FORM SUBMISSION & AUTH ROUTES ---
Route::post('/contact-us', [ContactController::class, 'send'])->name('contact.send');
Route::post('/listings/report/submit', [ReportController::class, 'store'])->name('listings.report.submit');
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
require __DIR__.'/auth.php';

// --- AUTHENTICATED USER ROUTES ---
Route::middleware(['auth', 'verified', 'isNotBlocked'])->group(function () {
    Route::post('/listing/{business:slug}/like', [PublicBusinessController::class, 'toggleLike'])->name('listings.like');
    Route::post('/blog/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');

    Route::get('/dashboard', function () {
        if (auth()->user()->isBusinessOwner()) { return redirect()->route('business-owner.dashboard'); }
        if (auth()->user()->isAdmin()) { return redirect()->route('admin.dashboard'); }
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/my-wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/business/toggle/{business:slug}', [WishlistController::class, 'toggleBusiness'])->name('wishlist.business.toggle');
    Route::post('/wishlist/event/toggle/{event:slug}', [WishlistController::class, 'toggleEvent'])->name('wishlist.event.toggle');

    Route::post('/listing/{business:slug}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/geocode/reverse', [GeocodingController::class, 'reverseGeocode'])->name('geocode.reverse');
    Route::post('/event/{event:slug}/review', [EventReviewController::class, 'store'])->name('events.reviews.store');
    Route::delete('/event-review/{eventReview}', [EventReviewController::class, 'destroy'])->name('events.reviews.destroy');

    // Itinerary Social & Management
    Route::get('/itineraries/create', [App\Http\Controllers\ItineraryController::class, 'create'])->name('itineraries.create');
    Route::post('/itineraries', [App\Http\Controllers\ItineraryController::class, 'store'])->name('itineraries.store');
    Route::post('/itinerary/{itinerary}/stops', [App\Http\Controllers\ItineraryController::class, 'addStop'])->name('itineraries.addStop');
    Route::post('/itinerary/{itinerary}/join', [App\Http\Controllers\ItineraryController::class, 'join'])->name('itineraries.join');
    Route::post('/itinerary/{itinerary}/like', [App\Http\Controllers\ItineraryController::class, 'like'])->name('itineraries.like');
    
    // Stop Management
    Route::patch('/itinerary-stops/{stop}', [App\Http\Controllers\ItineraryController::class, 'updateStop'])->name('itineraries.updateStop');
    Route::delete('/itinerary-stops/{stop}', [App\Http\Controllers\ItineraryController::class, 'deleteStop'])->name('itineraries.deleteStop');

    // Management
    Route::get('/itinerary/{itinerary}/edit', [App\Http\Controllers\ItineraryController::class, 'edit'])->name('itineraries.edit');
    Route::patch('/itinerary/{itinerary}', [App\Http\Controllers\ItineraryController::class, 'update'])->name('itineraries.update');
    Route::delete('/itinerary/{itinerary}', [App\Http\Controllers\ItineraryController::class, 'destroy'])->name('itineraries.destroy');
});

// --- CUSTOM ADMIN ROUTES ---
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('custom-admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Resources
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::resource('businesses', App\Http\Controllers\Admin\BusinessController::class);
        Route::delete('/businesses/{business}/media/{media}', [App\Http\Controllers\Admin\BusinessController::class, 'deleteMedia'])->name('businesses.media.destroy');
        Route::resource('events', App\Http\Controllers\Admin\EventController::class);
        Route::resource('posts', App\Http\Controllers\Admin\PostController::class);
        Route::resource('collections', App\Http\Controllers\Admin\DiscoveryCollectionController::class);
        Route::resource('reports', App\Http\Controllers\Admin\ReportController::class);
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('counties', App\Http\Controllers\Admin\CountyController::class);
        Route::resource('facilities', App\Http\Controllers\Admin\FacilityController::class);
        Route::resource('tags', App\Http\Controllers\Admin\TagController::class);
        Route::resource('hero-sliders', App\Http\Controllers\Admin\HeroSliderController::class);
        Route::resource('featured', App\Http\Controllers\Admin\FeaturedBusinessController::class);
        
        // Performance
        Route::get('/performance', [App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance/run', [App\Http\Controllers\Admin\PerformanceController::class, 'run'])->name('performance.run');
    });

// Business Owner Specific Routes
Route::middleware(['auth', 'verified', 'role:business_owner', 'isNotBlocked'])
    ->prefix('business-owner')
    ->name('business-owner.')
    ->group(function () {
        Route::get('/dashboard', [BusinessOwnerDashboardController::class, 'index'])->name('dashboard');
    });

// --- TEMPORARY SEO DEPLOYMENT ROUTE (FTP-ONLY WORKFLOW) ---
// Visit /deploy-seo-v2026?token=pizzel-seo-magic to trigger the database updates
Route::get('/deploy-seo-v2026', function(\Illuminate\Http\Request $request) {
    if ($request->query('token') !== 'pizzel-seo-magic') {
        abort(403, 'Unauthorized. Please check your deployment secret.');
    }

    $results = [];
    
    // ---------------------------------------------------------
    // 1. HUMAN-FIRST TITLE UPDATES (Fixing "Robotic" Formula)
    // ---------------------------------------------------------
    $titles = [
        'top-20-best-night-clubs-in-nairobi-2026-guide'     => 'Nairobi Nightlife Circuit: 20 Best Clubs for Afrobeats & Cocktails (2026)',
        'top-20-best-swimming-pools-in-kenya-2026-guide'    => 'Kenya Swimming & Aqua Park Guide: 20 Spots for Family Fun & Relaxation',
        'top-20-best-luxuries-in-kenya-2026-guide'          => 'Ultimate Kenya Luxury Guide: 20 Boutique Stays & 5-Star Retreats (2026)',
        'top-20-best-conference-centres-in-kenya-2026-guide' => 'Corporate Events Kenya: 20 Premier Conference Venues & Meeting Halls',
        'top-20-best-hidden-gems-in-kenya-2026-guide'       => 'Undiscovered Kenya: 20 Secret Spots & Hidden Gems for True Explorers',
        'top-18-best-wedding-venues-in-kenya-2026-guide'    => '18 Dreamy Wedding Venues in Kenya: Bush, Beach & Garden Options (2026)',
        'top-10-best-go-kartings-in-kenya-2026-guide'       => 'Kenya Karting Circuit: The Top 10 Professional & Family Racing Tracks (2026)'
    ];

    foreach ($titles as $slug => $title) {
        $count = \App\Models\DiscoveryCollection::where('slug', $slug)->update(['title' => $title]);
        $results[] = "Title Update [$slug]: " . ($count ? "SUCCESS" : "NO_CHANGE");
    }

    // ---------------------------------------------------------
    // 2. THE SENTIMENT ARCHITECT (Killing Boilerplate with Real Data)
    // ---------------------------------------------------------
    $pests = [
        'Karting is the safest, cheapest and, arguably, the best avenue into motorsports',
        'This is a point of interest located in',
        'establishment located in',
        'is a dentist located in',
    ];

    // Get businesses that either have boilerplate OR are missing a unique description
    $vulnerableBusinesses = \App\Models\Business::where(function($q) use ($pests) {
        foreach($pests as $p) {
            $q->orWhere('about_us', 'like', "%{$p}%");
        }
        $q->orWhereNull('about_us')->orWhere('about_us', '');
    })
    ->with(['googleReviews', 'categories', 'county', 'facilities'])
    ->take(150) // Batch process for performance
    ->get();

    foreach ($vulnerableBusinesses as $biz) {
        $loc = $biz->county->name ?? 'Kenya';
        $cat = $biz->categories->first()->name ?? 'Destination';
        $facilities = $biz->facilities->take(3)->pluck('name')->toArray();
        
        // --- 1. START WITH THE CORE ENTITY NARRATIVE ---
        $narrative = "<strong>{$biz->name}</strong> is a highly-rated {$cat} in {$loc}, offering a unique blend of quality service and local charm. ";

        // --- 2. INJECT REAL GOOGLE SENTIMENT (The "Information Gain" secret) ---
        $reviews = $biz->googleReviews->take(2)->pluck('text')->filter()->toArray();
        if (!empty($reviews)) {
            $sentimentSnippet = "";
            foreach ($reviews as $text) {
                // Get the first sentence of the review for brevity and flow
                $firstSentence = explode('.', $text)[0];
                if (strlen($firstSentence) > 20) {
                    $sentimentSnippet .= " Visitors have shared that \"<em>" . trim($firstSentence) . "...</em>\" ";
                }
            }
            if ($sentimentSnippet) {
                $narrative .= "The venue has earned a strong reputation among the community. " . $sentimentSnippet;
            }
        }

        // --- 3. INJECT AMENITIES NARRATIVE ---
        if (!empty($facilities)) {
             $narrative .= "For those planning a visit, {$biz->name} provides essential amenities including " . implode(', ', $facilities) . ", which greatly enhances the " . strtolower($cat) . " experience.";
        }

        // --- 4. THE VERDICT CONCLUSION ---
        $narrative .= " It remains an authoritative choice for travelers and residents seeking an authentic {$loc} vibe.";

        $biz->update(['about_us' => $narrative]);
        $results[] = "Sentiment Narrative Created: [{$biz->name}]";
    }

    // ---------------------------------------------------------
    // 3. THE GRAMMAR POLICE (Fixing "Go-Kartings")
    // ---------------------------------------------------------
    $badSuffixes = ['Kartings', 'Bikings', 'Paintballs', 'Trekkings', 'Campings', 'Hikings'];
    $collections = \App\Models\DiscoveryCollection::where(function($q) use ($badSuffixes) {
        foreach($badSuffixes as $bad) {
            $q->orWhere('title', 'like', "%{$bad}%");
        }
    })->get();

    foreach ($collections as $col) {
        $oldTitle = $col->title;
        // Fix: "Go-Kartings" -> "Go-Karting Venues"
        $newTitle = str_replace(
            ['Kartings', 'Bikings', 'Paintballs', 'Trekkings', 'Campings', 'Hikings', 'Top 10 Best', 'Top 20 Best'], 
            ['Karting Venues', 'Biking Trails', 'Paintball Fields', 'Trekking Routes', 'Camping Spots', 'Hiking Trails', '10 Best', '20 Best'], 
            $oldTitle
        );
        
        $col->update(['title' => $newTitle]);
        $results[] = "Grammar Fixed: '{$oldTitle}' -> '{$newTitle}'";
    }

    // ---------------------------------------------------------
    // 4. FORCE-OVERWRITE WHISTLING MORANS (No Checks, Just Do It)
    // ---------------------------------------------------------
    $wm = \App\Models\Business::where('slug', 'whistling-morans-ltd')->first();
    if ($wm) {
        $wm->update([
            'about_us' => "Whistling Morans in Athi River features one of the widest tracks in Kenya, making it perfect for high-speed overtaking and professional racing events. Unlike smaller mall-based circuits, this track offers a wide, professional-grade tarmac surface that allows for true competitive racing. It is a favorite for corporate team buildings and pro-karters alike because of its challenging curves and well-maintained fleet. The venue also features a swimming pool and restaurant, making it a full-day destination for families escaping Nairobi's hustle."
        ]);
        $results[] = "🔥 FORCE-UPDATED Whistling Morans Description";
    }

    return response()->json([
        'message' => '🚀 Scorched Earth Protocol Executed: Grammar Fixed & Content Purged.',
        'count' => count($results),
        'logs' => $results
    ]);
});
