<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Event::with('business');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $events = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.events._table', compact('events'))->render()
            ]);
        }

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $businesses = \App\Models\Business::orderBy('name')->get(); 
        // Logic: Events belong to businesses. Admin selects which business hosts the event.
        return view('admin.events.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'business_id' => 'required|exists:businesses,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'status' => 'required|in:pending_approval,active,cancelled,past',
            'description' => 'nullable|string',
        ]);

        $business = \App\Models\Business::find($request->business_id);

        \App\Models\Event::create([
            'title' => $request->title,
            'business_id' => $request->business_id,
            'user_id' => $business->user_id, // Owner of business is owner of event
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'status' => $request->status,
            'description' => $request->description,
             // Add other fields as defaults or extend form late
            'county_id' => $business->county_id, // Default to business location
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . rand(1000,9999),
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $businesses = \App\Models\Business::orderBy('name')->get();
        return view('admin.events.edit', compact('event', 'businesses'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:pending_approval,active,cancelled,past',
        ]);

        $event->update($request->all());

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }
}
