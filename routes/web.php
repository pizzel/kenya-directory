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
    
    // 1. UPDATE TITLES (Fix Keyword Cannibalization)
    $titles = [
        'top-20-best-night-clubs-in-nairobi-2026-guide' => 'Nairobi Nightlife Circuit: 20 Best Clubs for Afrobeats & Cocktails (2026)',
        'top-20-best-swimming-pools-in-kenya-2026-guide' => 'Kenya Swimming & Aqua Park Guide: 20 Spots for Family Fun & Relaxation',
        'top-20-best-luxuries-in-kenya-2026-guide' => 'Ultimate Kenya Luxury Guide: 20 Boutique Stays & 5-Star Retreats (2026)',
        'top-20-best-conference-centres-in-kenya-2026-guide' => 'Corporate Events Kenya: 20 Premier Conference Venues & Meeting Halls',
        'top-20-best-hidden-gems-in-kenya-2026-guide' => 'Undiscovered Kenya: 20 Secret Spots & Hidden Gems for True Explorers'
    ];

    foreach ($titles as $slug => $title) {
        $count = \App\Models\DiscoveryCollection::where('slug', $slug)->update(['title' => $title]);
        $results[] = "Title Update [$slug]: " . ($count ? "SUCCESS" : "NO_CHANGE");
    }

    // 2. UPDATE DESCRIPTIONS (Fix Entity Gaps)
    $intros = [
        'top-20-best-luxuries-in-kenya-2026-guide' => "Experience the pinnacle of hospitality in Kenya, where luxury accommodation transcends traditional lodging to become a holistic boutique stay experience. Our 2026 guide to Kenya's most prestigious retreats explores the delicate balance between modern amenities and indigenous architectural styles that anchor each property in its unique geographical context. From the historic elegance of Nairobi's leafy suburbs to the revolutionary luxury tents of the Great Rift Valley, these establishments offer curated wellness programs, world-class culinary journeys, and personalized concierge services. Each stay is designed as a sanctuary for those seeking a deep immersion into the region's diverse ecosystem without sacrificing comfort. Whether you are looking for a fine dining sanctuary or a secluded hideaway for a private excursion, these top-rated luxury spots prioritize guest privacy, high-end finishing, and an authentic connection to local culture through fine art and heritage. We have meticulously evaluated each venue for its commitment to conservation and biodiversity, ensuring that your stay supports the delicate balance of the Kenyan wild. This collection celebrates the 'Best of the Best', focusing on establishments that provide breathtaking panoramic views, exceptional service metrics, and the type of immersive storytelling that turns a simple vacation into a lifelong memory of the African savannah.",
        
        'top-20-best-night-clubs-in-nairobi-2026-guide' => "Nairobi's nightlife is a vibrant cluster of energy, authentic sounds, and social biodiversity that represents the modern heartbeat of East Africa. In our 2026 exploration of the city's after-dark scene, we dive deep into the cultural clusters that define the capital's entertainment district. From rooftop bars offering panoramic views of the CBD skyline to high-intensity night clubs featuring world-class DJs, this guide serves as your authoritative map to the city's social icons. We highlight venues that blend high-end ambiance with local flavors, focusing on the quality of sound systems, the expertise of mixology teams, and the safety briefings that ensure a secure environment for all explorers. Whether you are in search of a fine dining lounge for a corporate excursion or a hidden gem for an adrenaline-fueled night of dancing, Nairobi's bars and pubs provide a unique immersive experience. These spots often incorporate craft beverages and traditional appetizers, creating a culinary bridge between urban luxury and local heritage. Our selection emphasizes accessible spots with ample parking available, ensuring that your transition from office to evening is seamless. Discover the high-intent social spots that residents and tourists alike call home, and experience the thrilling rhythm that makes Nairobi a world-renowned destination for hospitality and nightlife.",
        
        'top-20-best-swimming-pools-in-kenya-2026-guide' => "As the Kenyan sun reaches its peak, nothing beats the refreshing immersion of the country's most spectacular swimming pools. Our 2026 aquatic guide explores the geographic diversity of Kenyan leisure, from the heated lap pools of the cool highlands to the stunning infinity pools overlooking the pristine beaches of the Indian Ocean. Whether you are planning a family-friendly staycation or a professional training session, these facilities offer more than just water; they provide clean, well-maintained environments anchored in hospitality excellence. We dive into the logistics of each site, highlighting amenities like poolside dining, accessible sunbeds, and professional safety briefings for younger explorers. This collection features a mix of resort pools, public water parks, and hidden gem plunges discovered off the regular tourist circuit. Many of these aquatic hubs are situated within larger nature reserves or luxury estates, providing panoramic views that integrate biodiversity with leisure. For those traveling for business, we have identified pools in the CBD with proximity to transport hubs, making a quick afternoon dip an easy addition to your itinerary. From the Great Rift Valley to the coastal region, experience the therapeutic benefits and the social vibe of Kenya's premium water-based activities, meticulously curated for quality, safety, and scenic value.",
        
        'top-20-best-conference-centres-in-kenya-2026-guide' => "In the rapidly evolving business ecosystem of East Africa, the right venue is the anchor for any successful corporate event. Our 2026 directory of the top conference centres in Kenya focuses on the critical logistics that drive professional excellence—accessibility, proximity to the CBD, and state-of-the-art technological amenities. We analyze each meeting hall based on its semantic importance to the regional economy, highlighting venues that offer seamless booking processes, ample parking available for high-capacity events, and reliable transport connections. These venues are not just strings of meeting rooms; they are authoritative landmarks that host international summits, strategic workshops, and high-stakes networking excursions. We provide insight into the onsite hospitality, from executive fine dining lunch options to the quality of the technical support gear provided. Whether your event is a boutique seminar in a leafy suburb or a massive convention at a world-class center, this guide helps you navigate the options based on intent and scale. Each listing includes qualitative data on the ambiance, the efficiency of the check-in process, and the proximity to high-end luxury accommodation for international delegates. By choosing from this curated collection, you ensure your professional event is positioned in a venue that reflects authority, efficiency, and the authentic spirit of Kenyan business hospitality.",
        
        'top-20-best-hidden-gems-in-kenya-2026-guide' => "To truly 'Discover Kenya', one must venture beyond the well-trodden safari circuits into the country's most secretive nature reserves and community conservancies. Our 2026 guide to hidden gems explores the semantic depth of the Kenyan landscape, focusing on biodiversity hubs and indigenous ecosystems that remain under the radar of mass tourism. These destinations offer an immersive experience into the flora and fauna of the Great Rift Valley, the coastal forests, and the arid northern frontiers. We highlight the 'Why' behind each location—whether it's an off-the-beaten-path hiking trail with scenic panoramic views or a secluded wildlife sanctuary dedicated to the conservation of rare species. This collection provides the descriptive connective tissue needed to understand the relationship between Kenya's varied topographies and its nomadic cultures. We focus on qualitative factors like the difficulty level of treks, the availability of specialized hiking guides, and the safety measures involved in remote excursions. These hidden gems are the ultimate Bucket List items for explorers seeking authentic, quiet, and meaningful interactions with nature. By documenting these landmarks, we anchor our directory in authoritative geographic knowledge, helping you plan a travel itinerary that captures the true, wild essence of the Kenyan wilderness while supporting local conservation initiatives and sustainable tourism."
    ];

    foreach ($intros as $slug => $desc) {
        $count = \App\Models\DiscoveryCollection::where('slug', $slug)->update(['description' => $desc]);
        $results[] = "Description Update [$slug]: " . ($count ? "SUCCESS" : "NO_CHANGE");
    }

    return response()->json([
        'message' => '🚀 Semantic SEO Database Synchronized!',
        'logs' => $results
    ]);
});
