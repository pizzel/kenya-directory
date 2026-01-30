<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\County;
use App\Models\EventCategory;
use Spatie\CalendarLinks\Link; // For Google Calendar link
use Spatie\IcalendarGenerator\Components\Calendar; // For ICS file
use Spatie\IcalendarGenerator\Components\Event as IcalendarEvent; // Alias to avoid conflict with App\Models\Event
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class PublicEventController extends Controller
{
	public function indexPublic(Request $request)
    {
        $eventTimeframe = $request->input('event_timeframe', 'upcoming'); // Default to 'upcoming'
        $sortOrder = $request->input('sort', 'default');

        $eventsQuery = Event::query()
                           ->where('status', 'active') // Always show active events
                           ->with([ // Eager load common relationships for cards
                               'business:id,name,slug', // Select specific fields for efficiency
                               'county:id,name,slug',
                               'categories:id,name,slug', // Event categories
                               'images' => function ($query) {
                                   $query->where('is_main_event_image', true)->limit(1);
                               }
                           ])
							->withCount(['reviews']) // Count all reviews
							->withAvg('reviews', 'rating'); // Average of all ratings, creates 'reviews_avg_rating'

        // 1. Apply Timeframe Filter (Upcoming, Past, All Active)
        if ($eventTimeframe === 'upcoming') {
            $eventsQuery->where('end_datetime', '>=', now());
        } elseif ($eventTimeframe === 'past') {
            $eventsQuery->where('end_datetime', '<', now());
        }
        // If 'all', no additional date filter beyond 'active' status is applied here for timeframe.

        // 2. Apply Keyword Filter (from sidebar search on events page)
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $eventsQuery->where(function (Builder $query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhereHas('business', function (Builder $businessQuery) use ($keyword) {
                          $businessQuery->where('name', 'like', "%{$keyword}%");
                      })
                      ->orWhereHas('categories', function (Builder $categoryQuery) use ($keyword) {
                          // Assuming event categories are linked, and 'name' is the field to search
                          $categoryQuery->where('name', 'like', "%{$keyword}%");
                      })
                      ->orWhereHas('county', function (Builder $countyQuery) use ($keyword) {
                          $countyQuery->where('name', 'like', "%{$keyword}%");
                      });
            });
        }

        // 3. Apply County Filter (from sidebar dropdown)
        if ($request->filled('county_filter_slug')) {
            $eventsQuery->whereHas('county', function (Builder $query) use ($request) {
                $query->where('slug', $request->input('county_filter_slug'));
            });
        }

        // 4. Apply Event Category (Activity) Filter (from sidebar checkboxes)
        if ($request->has('event_categories') && is_array($request->input('event_categories'))) {
            $categorySlugs = array_filter($request->input('event_categories')); // Remove empty values
            if (!empty($categorySlugs)) {
                $eventsQuery->whereHas('categories', function (Builder $query) use ($categorySlugs) {
                    $query->whereIn('slug', $categorySlugs);
                });
            }
        }

        // TODO: Add Price Filter if events have min_price/max_price
        // if ($request->filled('price_max')) { ... }

        // TODO: Add Rating Filter if events have average_rating
        // if ($request->filled('rating')) { ... }


        // 5. Apply Sorting
        // Always prioritize verified listings first if events have an is_verified flag
        // For events, 'is_verified' is less common than for businesses. Let's assume no direct 'is_verified' on Event model.
        // If events do have an is_verified, add: $eventsQuery->orderBy('is_verified', 'desc');

        if ($eventTimeframe === 'past') {
            // For past events, sort by most recently ended first (end_datetime descending)
            $eventsQuery->orderBy('end_datetime', 'desc');
        } elseif ($sortOrder === 'name_asc') {
            $eventsQuery->orderBy('title', 'asc');
        } elseif ($sortOrder === 'name_desc') {
            $eventsQuery->orderBy('title', 'desc');
        } else {
            // Default for 'upcoming' and 'all' is by soonest start_datetime
            $eventsQuery->orderBy('start_datetime', 'asc');
        }
        // Add a secondary sort for consistency if primary sort values are the same
        $eventsQuery->orderBy('id', 'desc');


        $events = $eventsQuery->paginate(12)->appends($request->query()); // Paginate results

        // Data for the filter sidebar
        $countiesForFilter = County::orderBy('name')->get();
        $eventCategoriesForFilter = EventCategory::orderBy('name')->get();
        // $facilitiesForFilter = Facility::orderBy('name')->get(); // If events can be filtered by facilities

        // Build page title
        $pageTitle = 'Events';
        if ($request->input('event_timeframe') === 'past') $pageTitle = 'Past Events';
        elseif ($request->input('event_timeframe') === 'upcoming') $pageTitle = 'Upcoming Events';
        else $pageTitle = 'All Active Events';

        if ($request->filled('keyword')) $pageTitle .= ' for "' . e($request->input('keyword')) . '"';
        // Add more context to title if other filters are active

        return view('events.index-public', compact(
            'events',
            'countiesForFilter',
            'eventCategoriesForFilter',
            // 'facilitiesForFilter',
            'request', // Pass the full request object for pre-filling filters
            'eventTimeframe', // Pass current timeframe for highlighting selected filter
            'pageTitle' // Pass a dynamically generated page title
        ));
    }

	
	 public function eventsByCounty(Request $request, string $countySlug)
    {
        $currentCounty = County::where('slug', $countySlug)->firstOrFail();
        $eventTimeframe = $request->input('event_timeframe', 'upcoming'); // Default to 'upcoming'
        $sortOrder = $request->input('sort', 'default');

        $eventsQuery = Event::query()
                           ->where('status', 'active')
                           ->where('county_id', $currentCounty->id) // Filter by the current county
                           ->with([ // Eager load common relationships for cards
                               'business:id,name', // Only select what's needed
                               'county:id,name,slug',
                               'categories:id,name,slug', // Event categories
                               'images' => function ($query) {
                                   $query->where('is_main_event_image', true)->limit(1);
                               }
                           ])
                           ->withCount('reviews') // Or your specific approved review count if applicable
                           ->withAvg('reviews', 'rating'); // Or your specific approved review avg if applicable

        // Apply Timeframe Filter
        if ($eventTimeframe === 'upcoming') {
            $eventsQuery->where('end_datetime', '>=', now());
            if ($sortOrder === 'default') $sortOrder = 'start_date_asc'; // Default for upcoming
        } elseif ($eventTimeframe === 'past') {
            $eventsQuery->where('end_datetime', '<', now());
            if ($sortOrder === 'default') $sortOrder = 'end_date_desc'; // Default for past
        }

        // Apply Keyword Filter (if you have a keyword search on this page's sidebar)
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $eventsQuery->where(function (Builder $query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhereHas('business', fn (Builder $bq) => $bq->where('name', 'like', "%{$keyword}%"))
                      ->orWhereHas('categories', fn (Builder $cq) => $cq->where('name', 'like', "%{$keyword}%"));
            });
        }

        // Apply Event Category (Activity) Filter (from sidebar checkboxes)
        if ($request->has('event_categories') && is_array($request->input('event_categories'))) {
            $categorySlugs = array_filter($request->input('event_categories'));
            if (!empty($categorySlugs)) {
                $eventsQuery->whereHas('categories', function (Builder $query) use ($categorySlugs) {
                    $query->whereIn('slug', $categorySlugs);
                });
            }
        }

        // Apply Sorting (ensure this method exists or move logic here)
        $eventsQuery = $this->applyEventSorting($eventsQuery, $sortOrder);

        $events = $eventsQuery->paginate(12)->appends($request->query());

        // Data for the filter sidebar
        // $countiesForFilter = County::orderBy('name')->get(); // Not needed if already on a county page, unless for "change county"
        $eventCategoriesForFilter = EventCategory::orderBy('name')->get();

        $pageTitle = 'Events in ' . $currentCounty->name;
        if ($eventTimeframe === 'past') $pageTitle = 'Past Events in ' . $currentCounty->name;
        if ($request->filled('keyword')) $pageTitle .= ' for "' . e($request->input('keyword')) . '"';


        return view('events.by-county', compact( // NEW VIEW
            'currentCounty', // Pass the county object
            'events',
            // 'countiesForFilter', // Only if you want a "change county" filter here
            'eventCategoriesForFilter',
            'request',
            'eventTimeframe',
            'pageTitle'
        ));
    }

    // You might need a similar applyEventSorting helper as in ListingController, or put logic inline
    private function applyEventSorting(Builder $query, string $sortOrder): Builder
    {
        // Events usually don't have 'is_verified' directly
        switch ($sortOrder) {
            case 'name_asc': $query->orderBy('title', 'asc'); break;
            case 'name_desc': $query->orderBy('title', 'desc'); break;
            case 'start_date_asc': $query->orderBy('start_datetime', 'asc'); break;
            case 'end_date_desc': $query->orderBy('end_datetime', 'desc'); break;
            // case 'rating_desc': $query->orderBy('reviews_avg_rating', 'desc'); break; // If using withAvg
            default: $query->orderBy('start_datetime', 'asc'); break;
        }
        $query->orderBy('id', 'desc'); // Secondary sort
        return $query;
    }
	
	
	
	
     

        

    /**
     * Display the specified event publicly.
     * (Your existing show method)
     */
    /**
     * Display the specified event publicly.
     *
     * @param  string  $eventSlug
     * @return \Illuminate\View\View
     */
   public function show(string $eventSlug)
    {
        $event = Event::where('slug', $eventSlug)
                      ->where('status', 'active')
                      ->with([
                          'business:id,name,slug',
                          'county:id,name,slug',
                          'categories:id,name,slug,icon_class',
                          'images' => function ($query) {
                              $query->orderBy('is_main_event_image', 'desc')
                                    ->orderBy('order', 'asc')
                                    ->orderBy('id', 'asc');
                          },
                          'reviews' => function ($query) { // Eager load reviews
                              $query->with('user')->latest(); // CORRECTED: No 'is_approved' filter
                          }
                      ])
                      ->firstOrFail();

        // Increment view count if method exists
        if (method_exists($event, 'incrementViews')) {
            $event->incrementViews();
        }

        // --- PREPARE GALLERY IMAGES (as before) ---
        $galleryImages = collect();
        if ($event->images->isNotEmpty()) {
            $mainImageForGallery = $event->images->firstWhere('is_main_event_image', true) ?? $event->images->first();
            if ($mainImageForGallery) {
                $galleryImages->push($mainImageForGallery);
                $otherImagesForGallery = $event->images->filter(fn($img) => $img->id !== $mainImageForGallery->id);
                $galleryImages = $galleryImages->merge($otherImagesForGallery);
            } else {
                $galleryImages = $event->images;
            }
        }

        // --- SIMILAR EVENTS (ensure reviews count/avg here also don't use is_approved if it's removed) ---
        $categoryIds = $event->categories->pluck('id');
		$similarEvents = Event::activeAndUpcoming()
			->where('status', 'active')
            ->where('id', '!=', $event->id)
            ->when($categoryIds->isNotEmpty(), function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($subQuery) use ($categoryIds) {
                    $subQuery->whereIn('event_categories.id', $categoryIds);
                });
            })
            ->with(['business:id,name', 'county:id,name', 'images' => fn($q) => $q->where('is_main_event_image', true)->limit(1)])
            // If Event model has average_rating & reviews_count, they should be calculated from ALL reviews now
            // Or use withCount('reviews') and withAvg('reviews', 'rating') if calculating here
            ->withCount('reviews') // Counts all reviews
            ->withAvg('reviews', 'rating') // Averages all review ratings, creates 'reviews_avg_rating'
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('events.show-public', compact(
            'event',
            'galleryImages',
            'similarEvents'
        ));
    }
	public function downloadIcs(string $eventSlug)
    {
        $event = Event::where('slug', $eventSlug)
                      ->where('status', 'active') // Only allow for active events
                      ->firstOrFail();

			// Ensure start and end datetimes are Carbon instances
			$from = ($event->start_datetime instanceof Carbon) ? $event->start_datetime : Carbon::parse($event->start_datetime);
			$to = ($event->end_datetime instanceof Carbon) ? $event->end_datetime : Carbon::parse($event->end_datetime);

			$description = "Event: " . $event->title . "\n";
			if ($event->description) {
				$description .= "Details: " . strip_tags($event->description) . "\n";
			}
			if ($event->address || $event->county) {
				$locationParts = [];
				if ($event->address) $locationParts[] = $event->address;
				if ($event->county) $locationParts[] = $event->county->name;
				$description .= "Location: " . implode(', ', $locationParts) . "\n";
			}
			if ($event->ticketing_url) {
				$description .= "Tickets/More Info: " . $event->ticketing_url . "\n";
			}
			$description .= "View Event: " . route('events.show.public', $event->slug);


			$calendarEvent = IcalendarEvent::create($event->title)
				->description($description)
				->startsAt($from)
				->endsAt($to);

			if ($event->address || $event->county) {
				$locationString = $event->address ?? '';
				if ($event->county && $event->address) $locationString .= ', ';
				if ($event->county) $locationString .= $event->county->name;
				$calendarEvent->address($locationString);
			}

			// Create the calendar
			$calendar = Calendar::create(config('app.name', 'Discover Kenya') . ' Event')
				->event($calendarEvent);

			return response($calendar->get())
				->header('Content-Type', 'text/calendar; charset=utf-8')
				->header('Content-Disposition', 'attachment; filename="' . Str::slug($event->title) . '.ics"');
		}
	
	
	
}