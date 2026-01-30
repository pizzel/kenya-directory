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
Route::get('/react-test', function () {
    return inertia('Home');
});
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
        Route::resource('businesses', BusinessOwnerBusinessController::class);
        Route::resource('events', BusinessOwnerEventController::class);
    });