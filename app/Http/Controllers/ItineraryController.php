<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use App\Models\ItineraryStop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ItineraryController extends Controller
{
    /**
     * Display a listing of public itineraries.
     */
    public function index()
    {
        $itineraries = Itinerary::where('visibility', 'public')
            ->with(['creator', 'stops.business.media']) // Eager load business media for card images
            ->withCount(['participants', 'likers'])
            ->latest()
            ->paginate(12);

        return view('itineraries.index', compact('itineraries'));
    }

    public function show(Itinerary $itinerary)
    {
        // Load relationships with media for images
        $itinerary->load([
            'creator', 
            'stops.business.media', // Load business media for images
            'stops.county'
        ]);
        $itinerary->loadCount(['participants', 'likers']);

        // Check if current user is participating or liked
        $isParticipating = auth()->check() ? $itinerary->participants()->where('user_id', auth()->id())->exists() : false;
        $isLiked = auth()->check() ? $itinerary->likers()->where('user_id', auth()->id())->exists() : false;

        return view('itineraries.show', compact('itinerary', 'isParticipating', 'isLiked'));
    }

    /**
     * Show the form for creating a new itinerary.
     */
    public function create()
    {
        return view('itineraries.create');
    }

    /**
     * Store a newly created itinerary in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'required|in:public,private,unlisted',
        ]);

        $itinerary = Itinerary::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'visibility' => $request->visibility,
            'theme_color' => $request->theme_color ?? '#3b82f6',
        ]);

        return redirect()->route('itineraries.show', $itinerary->slug)
            ->with('success', 'Itinerary created! Now add some stops.');
    }

    public function addStop(Request $request, Itinerary $itinerary)
    {
        // Only owner can add stops
        if ($itinerary->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'location_name' => 'nullable|string',
            'business_ids' => 'nullable|string', // Comma-separated IDs
        ]);

        // Get the first business ID if multiple were selected
        $businessIds = $request->business_ids ? explode(',', $request->business_ids) : [];
        $primaryBusinessId = !empty($businessIds) ? (int)$businessIds[0] : null;

        $stop = $itinerary->stops()->create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location_name' => $request->location_name,
            'business_id' => $primaryBusinessId, // Use first business for image
            'image_url' => null, // Will use business image via getDisplayImageAttribute
        ]);

        // Update Itinerary start/end dates
        $minDate = $itinerary->stops()->min('start_time');
        $maxDate = $itinerary->stops()->max('end_time') ?? $itinerary->stops()->max('start_time');
        $itinerary->update([
            'start_date' => $minDate,
            'end_date' => $maxDate
        ]);

        return back()->with('success', 'Stop added successfully!');
    }

    /**
     * Join/Leave an itinerary.
     */
    public function join(Itinerary $itinerary)
    {
        $user = auth()->user();
        
        if ($itinerary->participants()->where('user_id', $user->id)->exists()) {
            $itinerary->participants()->detach($user->id);
            $joined = false;
        } else {
            $itinerary->participants()->attach($user->id, ['status' => 'going']);
            $joined = true;
        }

        return response()->json([
            'success' => true,
            'joined' => $joined,
            'count' => $itinerary->participants()->count()
        ]);
    }

    /**
     * Like/Unlike an itinerary.
     */
    public function like(Itinerary $itinerary)
    {
        $user = auth()->user();
        
        if ($itinerary->likers()->where('user_id', $user->id)->exists()) {
            $itinerary->likers()->detach($user->id);
            $liked = false;
        } else {
            $itinerary->likers()->attach($user->id);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'count' => $itinerary->likers()->count()
        ]);
    }

    public function edit(Itinerary $itinerary)
    {
        if ($itinerary->user_id !== auth()->id()) {
            abort(403);
        }
        return view('itineraries.edit', compact('itinerary'));
    }

    public function update(Request $request, Itinerary $itinerary)
    {
        if ($itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'required|in:public,private,unlisted',
        ]);

        $itinerary->update([
            'title' => $request->title,
            'description' => $request->description,
            'visibility' => $request->visibility,
            'theme_color' => $request->theme_color ?? $itinerary->theme_color,
        ]);

        return redirect()->route('itineraries.show', $itinerary->slug)
            ->with('success', 'Itinerary updated successfully!');
    }

    public function destroy(Itinerary $itinerary)
    {
        if ($itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        $itinerary->delete();

        return redirect()->route('itineraries.index')
            ->with('success', 'Journey has been deleted.');
    }

    public function updateStop(Request $request, ItineraryStop $stop)
    {
        $itinerary = $stop->itinerary;
        if ($itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'location_name' => 'nullable|string',
            'business_ids' => 'nullable|string',
        ]);

        $businessIds = $request->business_ids ? explode(',', $request->business_ids) : [];
        $primaryBusinessId = !empty($businessIds) ? (int)$businessIds[0] : $stop->business_id;

        $stop->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location_name' => $request->location_name,
            'business_id' => $primaryBusinessId,
        ]);

        // Re-calculate Itinerary dates
        $this->syncItineraryDates($itinerary);

        return back()->with('success', 'Stop updated successfully!');
    }

    public function deleteStop(ItineraryStop $stop)
    {
        $itinerary = $stop->itinerary;
        if ($itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        $stop->delete();

        // Re-calculate Itinerary dates
        $this->syncItineraryDates($itinerary);

        return back()->with('success', 'Stop removed.');
    }

    protected function syncItineraryDates($itinerary)
    {
        $minDate = $itinerary->stops()->min('start_time');
        $maxDate = $itinerary->stops()->max('end_time') ?? $itinerary->stops()->max('start_time');
        
        $itinerary->update([
            'start_date' => $minDate,
            'end_date' => $maxDate
        ]);
    }
}
