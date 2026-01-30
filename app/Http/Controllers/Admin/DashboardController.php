<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Business;
use App\Models\Event;
use App\Models\Report;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'businesses' => Business::count(),
            'events' => Event::count(),
            'reports' => Report::where('status', 'pending')->count(),
        ];

        $topCategories = \App\Models\Category::withCount('businesses')
            ->orderBy('businesses_count', 'desc')
            ->take(5)
            ->get();

        $recentUsers = User::latest()->take(5)->get();
        $recentBusinesses = Business::with('owner', 'categories')->latest()->take(5)->get();
        $recentReports = Report::with(['user', 'business', 'event'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 
            'recentUsers', 
            'recentBusinesses', 
            'recentReports', 
            'topCategories'
        ));
    }
}
