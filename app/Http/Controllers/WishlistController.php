<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    private function authorizeRegularUser()
    {
        if (Auth::user()->role !== 'user') {
            abort(403, 'This feature is only available for standard users.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeRegularUser();
        $user = Auth::user();

        // 1. Fetch Bucket List (Paginated)
        // We use a custom page name 'bucket_page' to avoid conflicts with other tabs
        $bucketList = $user->wishlistedBusinesses()
            ->wherePivot('status', 'wished')
            ->with(['county', 'media']) 
            ->orderByPivot('created_at', 'desc')
            ->paginate(15, ['*'], 'bucket_page');

        // 2. Fetch Visited Places (Paginated)
        $visitedPlaces = $user->wishlistedBusinesses()
            ->wherePivot('status', 'done')
            ->with(['county', 'media'])
            ->orderByPivot('updated_at', 'desc') 
            ->paginate(15, ['*'], 'visited_page');

        // 3. Fetch Joined Journeys (Paginated)
        $joinedJourneys = $user->joinedItineraries()
            ->with(['creator', 'stops.business.county'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(15, ['*'], 'journeys_page');

        // 4. Calculate Stats & Get County Names
        // Since $visitedPlaces is now paginated, we need a separate lightweight query 
        // to get ALL visited counties for the "Passport Stamps" section.
        $allVisited = $user->wishlistedBusinesses()
            ->wherePivot('status', 'done')
            ->with('county:id,name') // Optimize: only select necessary columns
            ->get();

        $visitedCounties = $allVisited->pluck('county.name')->unique()->sort()->values();

        $stats = [
            // Use ->total() on paginators to get the full count, not just the 15 on page
            'bucket_count' => $bucketList->total(),
            'visited_count' => $visitedPlaces->total(),
            'total_count' => $bucketList->total() + $visitedPlaces->total(),
            'counties_covered' => $visitedCounties->count()
        ];

        return view('wishlist.index', compact('bucketList', 'visitedPlaces', 'joinedJourneys', 'stats', 'visitedCounties'));
    }

    // ... toggleBusiness and toggleEvent methods remain unchanged ...
    public function toggleBusiness(Request $request, Business $business)
    {
        $this->authorizeRegularUser();
        $user = Auth::user();
        $action = $request->input('action', 'add');
        $successMessage = '';
        
        $wishlistItem = $user->wishlistedBusinesses()->where('business_id', $business->id)->first();

        if ($action === 'remove') {
            if ($wishlistItem) {
                $user->wishlistedBusinesses()->detach($business->id);
                $successMessage = 'Removed from your travel passport.';
            }
        } 
        elseif ($action === 'toggle_done') {
            if ($wishlistItem) {
                $targetStatus = $request->input('status_target') 
                    ?? ($wishlistItem->pivot->status === 'done' ? 'wished' : 'done');

                $user->wishlistedBusinesses()->updateExistingPivot($business->id, ['status' => $targetStatus]);
                
                $successMessage = ($targetStatus === 'done') 
                    ? 'Marked as Visited! Great job exploring.' 
                    : 'Moved back to Bucket List.';
            }
        } 
        elseif ($action === 'add') {
            if (!$wishlistItem) {
                $user->wishlistedBusinesses()->attach($business->id, ['status' => 'wished']);
                $successMessage = 'Added to your Bucket List!';
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            // Re-fetch to get fresh state
            $finalItem = $user->wishlistedBusinesses()->where('business_id', $business->id)->first();
            $isInWishlist = (bool) $finalItem;
            $isDone = $isInWishlist && $finalItem->pivot->status === 'done';
            
            // NEW: Fetch updated counts to send back to the frontend
            $bucketCount = $user->wishlistedBusinesses()->wherePivot('status', 'wished')->count();
            $visitedCount = $user->wishlistedBusinesses()->wherePivot('status', 'done')->count();
            
            return response()->json([
                'success' => true, 
                'is_in_wishlist' => $isInWishlist, 
                'is_done' => $isDone, 
                'message' => $successMessage,
                'bucket_count' => $bucketCount,   // Send new counts
                'visited_count' => $visitedCount  // Send new counts
            ]);
        }

        return back()->with('success', $successMessage);
    }

    public function toggleEvent(Request $request, Event $event)
    {
         // ... existing logic ...
        $this->authorizeRegularUser();
        $user = Auth::user();
        $action = $request->input('action', 'add');
        $successMessage = '';

        $wishlistItem = $user->wishlistedEvents()->where('event_id', $event->id)->first();

        if ($action === 'remove') {
            if ($wishlistItem) {
                $user->wishlistedEvents()->detach($event->id);
                $successMessage = 'Event removed from list.';
            }
        } elseif ($action === 'add') {
            if (!$wishlistItem) {
                $user->wishlistedEvents()->attach($event->id, ['status' => 'wished']);
                $successMessage = 'Event saved!';
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            $finalItem = $user->wishlistedEvents()->where('event_id', $event->id)->first();
            return response()->json([
                'success' => true, 
                'is_in_wishlist' => (bool)$finalItem, 
                'message' => $successMessage
            ]);
        }
        
        return back()->with('success', $successMessage);
    }
}