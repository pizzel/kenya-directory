<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\County;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::with(['owner', 'county', 'media']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $businesses = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.businesses._table', compact('businesses'))->render()
            ]);
        }

        return view('admin.businesses.index', compact('businesses'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(); // Admin can assign to any user
        $counties = County::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $facilities = \App\Models\Facility::orderBy('name')->get();
        $tags = \App\Models\Tag::orderBy('name')->get();

        return view('admin.businesses.create', compact('users', 'counties', 'categories', 'facilities', 'tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'county_id' => 'required|exists:counties,id',
            'status' => 'required|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'address' => 'required|string',
        ]);

        $data = $request->except(['categories', 'facilities', 'tags']);
        $data['slug'] = Str::slug($data['name']);
        $data['is_verified'] = $request->has('is_verified');
        $data['is_featured'] = $request->has('is_featured');
        
        $business = Business::create($data);

        if ($request->has('categories')) {
            $business->categories()->sync($request->input('categories'));
        }
        if ($request->has('facilities')) {
            $business->facilities()->sync($request->input('facilities'));
        }
        if ($request->has('tags')) {
            $business->tags()->sync($request->input('tags'));
        }

        // Handle Image Uploads with Professional Naming
        if ($request->hasFile('images')) {
            $countyName = $business->county->name ?? '';
            foreach ($request->file('images') as $image) {
                $filename = Str::slug($business->name . '-' . $countyName . '-' . uniqid()) . '.' . $image->getClientOriginalExtension();
                $business->addMedia($image)
                    ->usingFileName($filename)
                    ->toMediaCollection('images');
            }
        }

        return redirect()->route('admin.businesses.index')->with('success', 'Business created successfully.');
    }

    public function edit(Business $business)
    {
        $users = User::orderBy('name')->get();
        $counties = County::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $facilities = \App\Models\Facility::orderBy('name')->get();
        $tags = \App\Models\Tag::orderBy('name')->get();
        
        $business->load(['categories', 'facilities', 'tags']);

        return view('admin.businesses.edit', compact('business', 'users', 'counties', 'categories', 'facilities', 'tags'));
    }

    public function update(Request $request, Business $business)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'county_id' => 'required|exists:counties,id',
            'status' => 'required|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'phone_number' => 'nullable|string',
            'address' => 'required|string',
        ]);

        $data = $request->except(['categories', 'facilities', 'tags']);
        $data['is_verified'] = $request->has('is_verified');
        $data['is_featured'] = $request->has('is_featured');

        $business->update($data);

        $business->categories()->sync($request->input('categories', []));
        $business->facilities()->sync($request->input('facilities', []));
        $business->tags()->sync($request->input('tags', []));

        // Handle Image Uploads with Professional Naming
        if ($request->hasFile('images')) {
            $countyName = $business->county->name ?? '';
            foreach ($request->file('images') as $image) {
                $filename = Str::slug($business->name . '-' . $countyName . '-' . uniqid()) . '.' . $image->getClientOriginalExtension();
                $business->addMedia($image)
                    ->usingFileName($filename)
                    ->toMediaCollection('images');
            }
        }

        return redirect()->back()->with('success', 'Business updated successfully.');
    }

    public function deleteMedia(Business $business, $mediaId)
    {
        $media = $business->getMedia('images')->find($mediaId);
        if ($media) {
            $media->delete();
            return redirect()->back()->with('success', 'Image deleted successfully.');
        }
        return redirect()->back()->with('error', 'Image not found.');
    }

    public function destroy(Business $business)
    {
        $business->delete();
        return redirect()->route('admin.businesses.index')->with('success', 'Business deleted successfully.');
    }
}
