<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index(Request $request)
    {
        $query = County::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $counties = $query->orderBy('name')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.counties._table', compact('counties'))->render()
            ]);
        }

        return view('admin.counties.index', compact('counties'));
    }

    public function create()
    {
        return view('admin.counties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:counties,name',
        ]);

        County::create($request->all());

        return redirect()->route('admin.counties.index')->with('success', 'County created successfully.');
    }

    public function edit(County $county)
    {
        return view('admin.counties.edit', compact('county'));
    }

    public function update(Request $request, County $county)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $county->id,
        ]);

        $county->update($request->all());

        return redirect()->route('admin.counties.index')->with('success', 'County updated successfully.');
    }

    public function destroy(County $county)
    {
        $county->delete();
        return redirect()->route('admin.counties.index')->with('success', 'County deleted successfully.');
    }
}
