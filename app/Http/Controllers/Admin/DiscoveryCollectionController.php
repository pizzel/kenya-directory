<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscoveryCollection;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscoveryCollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = DiscoveryCollection::withCount('businesses')->with(['coverBusiness.media']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $collections = $query->orderBy('display_order')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.collections._table', compact('collections'))->render()
            ]);
        }

        return view('admin.collections.index', compact('collections'));
    }

    public function create()
    {
        $businesses = Business::where('status', 'active')->orderBy('name')->get();
        return view('admin.collections.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'businesses' => 'nullable|array',
            'businesses.*' => 'exists:businesses,id',
        ]);

        $data = $request->except('businesses');
        $data['slug'] = Str::slug($data['title']);
        $data['is_active'] = $request->has('is_active'); // Checkbox handling

        $collection = DiscoveryCollection::create($data);

        if ($request->has('businesses')) {
            $collection->businesses()->sync($request->input('businesses'));
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection created successfully.');
    }

    public function edit(DiscoveryCollection $collection)
    {
        $businesses = Business::where('status', 'active')->orderBy('name')->get();
        return view('admin.collections.edit', compact('collection', 'businesses'));
    }

    public function update(Request $request, DiscoveryCollection $collection)
    {
         $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'businesses' => 'nullable|array',
            'businesses.*' => 'exists:businesses,id',
        ]);

        $data = $request->except('businesses');
        $data['is_active'] = $request->has('is_active');

        $collection->update($data);

        if ($request->has('businesses')) {
            $collection->businesses()->sync($request->input('businesses'));
        } else {
             $collection->businesses()->detach();
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(DiscoveryCollection $collection)
    {
        $collection->delete();
        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }
}
