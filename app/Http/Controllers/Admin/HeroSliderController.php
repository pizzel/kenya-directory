<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSliderHistory;
use App\Models\Business;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HeroSliderController extends Controller
{
    public function index(Request $request)
    {
        // Eager load everything to stop N+1 queries and make it load instantly
        $query = HeroSliderHistory::with(['business.county', 'business.media'])->orderBy('activated_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('business', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $now = now();
            if ($status === 'active') {
                $query->where('activated_at', '<=', $now)
                      ->where('set_to_expire_at', '>', $now);
            } elseif ($status === 'expired') {
                $query->where('set_to_expire_at', '<=', $now);
            } elseif ($status === 'scheduled') {
                $query->where('activated_at', '>', $now);
            }
        }

        $sliders = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.hero-sliders._table', compact('sliders'));
        }

        return view('admin.hero-sliders.index', compact('sliders'));
    }

    public function create()
    {
        // Surgical selection to keep the dropdown fast and lightweight
        $businesses = Business::select('id', 'name', 'county_id')
            ->with('county:id,name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        return view('admin.hero-sliders.create', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'activated_at' => 'required|date',
            'set_to_expire_at' => 'required|date|after:activated_at',
            'amount_paid' => 'nullable|numeric|min:0',
            'package_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        HeroSliderHistory::create([
            'business_id' => $request->business_id,
            'admin_id' => Auth::id(),
            'activated_at' => $request->activated_at,
            'set_to_expire_at' => $request->set_to_expire_at,
            'amount_paid' => $request->amount_paid ?? 0,
            'package_name' => $request->package_name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.hero-sliders.index')->with('success', 'Hero Slider scheduled successfully.');
    }

    public function edit(HeroSliderHistory $heroSlider)
    {
        $businesses = Business::select('id', 'name', 'county_id')
            ->with('county:id,name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        return view('admin.hero-sliders.edit', compact('heroSlider', 'businesses'));
    }

    public function update(Request $request, HeroSliderHistory $heroSlider)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'activated_at' => 'required|date',
            'set_to_expire_at' => 'required|date|after:activated_at',
            'amount_paid' => 'nullable|numeric|min:0',
            'package_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $heroSlider->update([
            'business_id' => $request->business_id,
            'activated_at' => $request->activated_at,
            'set_to_expire_at' => $request->set_to_expire_at,
            'amount_paid' => $request->amount_paid ?? 0,
            'package_name' => $request->package_name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.hero-sliders.index')->with('success', 'Hero Slider updated successfully.');
    }

    public function destroy(HeroSliderHistory $heroSlider)
    {
        $heroSlider->delete();
        return response()->json(['success' => true]);
    }
}
