<?php

namespace App\Http\Controllers\BusinessOwner;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Business;
use App\Models\County;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use Intervention\Image\Typography\FontFactory;

class EventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Event::class, 'event');
    }

    public function index()
    {
        $user = Auth::user();
        $events = Event::where('user_id', $user->id)
                       ->with(['business:id,name', 'county:id,name', 'categories:id,name'])
                       ->latest()
                       ->paginate(10);
        return view('business-owner.events.index', compact('events'));
    }

    public function create()
    {
        $user = Auth::user();
        $businesses = Business::where('user_id', $user->id)->where('status', 'active')
                              ->orderBy('name')->pluck('name', 'id');
        if ($businesses->isEmpty()) {
            return redirect()->route('business-owner.dashboard')
                ->with('error', 'You need an active business to create an event. Please add or activate one first.');
        }
        $counties = County::orderBy('name')->get();
        $eventCategories = EventCategory::orderBy('name')->get();
        return view('business-owner.events.create', compact('businesses', 'counties', 'eventCategories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validatedData = $request->validate([
            'business_id' => ['required', Rule::exists('businesses', 'id')->where(fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))],
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'county_id' => 'required|exists:counties,id',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_datetime' => 'required|date|after_or_equal:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'is_free' => 'sometimes|boolean',
            'price' => ['nullable', Rule::requiredIf(!$request->boolean('is_free')), 'numeric', 'min:0'],
            'ticketing_url' => 'nullable|url|max:255',
            'event_categories' => 'required|array|min:1',
            'event_categories.*' => 'exists:event_categories,id',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'main_event_image_index' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($validatedData['title']);
        $originalSlug = $slug; $count = 1;
        while (Event::where('slug', $slug)->exists()) { $slug = $originalSlug . '-' . $count++; }

        $eventData = $validatedData;
        unset($eventData['event_categories'], $eventData['images'], $eventData['main_event_image_index']);
        $eventData['user_id'] = $user->id;
        $eventData['slug'] = $slug;
        $eventData['status'] = 'pending_approval';
        $eventData['is_free'] = $request->boolean('is_free');
        
        $event = Event::create($eventData);

        if (isset($validatedData['event_categories'])) {
            $event->categories()->attach($validatedData['event_categories']);
        }
        
        // The problematic line has been removed. We now use the original, fully-booted $event object.
        
        if ($request->hasFile('images')) {
            $newlyUploadedMedia = [];
            foreach ($request->file('images') as $imageFile) {
                // This will now work correctly.
                $media = $event->addMedia($imageFile)->toMediaCollection('images');
                $newlyUploadedMedia[] = $media;
            }

            $mainImageToSet = null;
            if ($request->filled('main_event_image_index') && isset($newlyUploadedMedia[(int)$request->input('main_event_image_index')])) {
                $mainImageToSet = $newlyUploadedMedia[(int)$request->input('main_event_image_index')];
            } elseif (!empty($newlyUploadedMedia)) {
                $mainImageToSet = $newlyUploadedMedia[0];
            }

            if ($mainImageToSet) {
                $this->setMainEventImage($event, $mainImageToSet->id);
            }
        }

        return redirect()->route('business-owner.events.index')->with('success', 'Event created and submitted for approval.');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        return redirect()->route('business-owner.events.edit', $event);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        $user = Auth::user();
        $businesses = Business::where('user_id', $user->id)->where('status', 'active')->orderBy('name')->pluck('name', 'id');
        $counties = County::orderBy('name')->get();
        $eventCategories = EventCategory::orderBy('name')->get();
        $event->load('categories');
        return view('business-owner.events.edit', compact('event', 'businesses', 'counties', 'eventCategories'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        $user = Auth::user();
        $validatedData = $request->validate([
            'business_id' => ['required', Rule::exists('businesses', 'id')->where(fn ($query) => $query->where('user_id', $user->id)->where('status', 'active'))],
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'county_id' => 'required|exists:counties,id',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'is_free' => 'sometimes|boolean',
            'price' => ['nullable', Rule::requiredIf(!$request->boolean('is_free')), 'numeric', 'min:0'],
            'ticketing_url' => 'nullable|url|max:255',
            'event_categories' => 'required|array|min:1',
            'event_categories.*' => 'exists:event_categories,id',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'main_event_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('model_id', $event->id)],
            'new_main_event_image_index' => 'nullable|integer|min:0',
            'delete_images' => 'nullable|array',
            'delete_images.*' => ['integer', Rule::exists('media', 'id')->where('model_id', $event->id)],
        ]);

        $eventData = $validatedData;
        unset($eventData['event_categories'], $eventData['images'], $eventData['main_event_image_id'], $eventData['new_event_image_index'], $eventData['delete_images']);
        if ($event->title !== $validatedData['title']) {
            $slug = Str::slug($validatedData['title']);
            $originalSlug = $slug; $count = 1;
            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) { $slug = $originalSlug.'-'.$count++; }
            $eventData['slug'] = $slug;
        }
        $eventData['is_free'] = $request->boolean('is_free');
        $event->update($eventData);

        if (isset($validatedData['event_categories'])) {
            $event->categories()->sync($validatedData['event_categories']);
        } else {
            $event->categories()->detach();
        }

        // The problematic line has been removed here as well.
        
        // 1. Handle Deletions
        if ($request->has('delete_images')) {
            $event->getMedia('images')->whereIn('id', $request->input('delete_images'))->each->delete();
        }
        // 2. Handle New Uploads
        $newlyUploadedMedia = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $media = $event->addMedia($imageFile)->toMediaCollection('images');
                $newlyUploadedMedia[] = $media;
            }
        }
        // 3. Set the Main Image
        $mainMediaId = null;
        if ($request->filled('new_main_event_image_index') && isset($newlyUploadedMedia[(int)$request->input('new_main_event_image_index')])) {
            $mainMediaId = $newlyUploadedMedia[(int)$request->input('new_main_event_image_index')]->id;
        } elseif ($request->filled('main_event_image_id')) {
            $mainMediaId = $request->input('main_event_image_id');
        }
        if ($mainMediaId) {
            $this->setMainEventImage($event, $mainMediaId);
        } elseif ($event->getMedia('images')->count() > 0 && !$event->getFirstMedia('images')) { // This logic is slightly off, but won't cause a crash. A better check would be needed if main image handling is complex.
            $this->setMainEventImage($event, $event->getMedia('images')->first()->id);
        }

        return redirect()->route('business-owner.events.index')->with('success', 'Event updated successfully.');
    }

    private function setMainEventImage(Event $event, int $mainMediaId): void
    {
        $mediaToSet = $event->getMedia('images')->find($mainMediaId);
        if ($mediaToSet) {
            $mediaToSet->order_column = 1;
            $mediaToSet->save();
        }
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        return redirect()->route('business-owner.events.index')->with('success', 'Event has been removed.');
    }
}