<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeaturedBusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::with(['categories', 'county', 'media'])->where('is_featured', true)->orderBy('featured_expires_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $businesses = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.featured._table', compact('businesses'));
        }

        return view('admin.featured.index', compact('businesses'));
    }

    public function create()
    {
        // Surgical selection to keep the dropdown fast and lightweight
        $availableBusinesses = Business::select('id', 'name', 'county_id')
            ->with('county:id,name')
            ->where('is_featured', false)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.featured.create', compact('availableBusinesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'featured_expires_at' => 'required|date|after:now',
        ]);

        $business = Business::findOrFail($request->business_id);
        $business->update([
            'is_featured' => true,
            'featured_expires_at' => $request->featured_expires_at,
        ]);

        return redirect()->route('admin.featured.index')->with('success', 'Business added to featured listings!');
    }

    public function edit(Business $featured)
    {
        // The parameter is named 'featured' to match the resource route
        $business = $featured;
        return view('admin.featured.edit', compact('business'));
    }

    public function update(Request $request, Business $featured)
    {
        $request->validate([
            'featured_expires_at' => 'required|date',
        ]);

        $featured->update([
            'featured_expires_at' => $request->featured_expires_at,
            'is_featured' => Carbon::parse($request->featured_expires_at)->isFuture(),
        ]);

        return redirect()->route('admin.featured.index')->with('success', 'Featured expiration updated.');
    }

    public function destroy(Business $featured)
    {
        $featured->update([
            'is_featured' => false,
            'featured_expires_at' => null,
        ]);

        return response()->json(['success' => true]);
    }
}
