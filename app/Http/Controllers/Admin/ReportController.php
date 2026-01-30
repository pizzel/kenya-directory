<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Report::with(['user', 'business', 'event']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('report_reason', 'like', "%{$search}%")
                  ->orWhereHas('business', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('event', function($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%");
                  });
        }

        $reports = $query->latest()->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.reports._table', compact('reports'))->render()
            ]);
        }

        return view('admin.reports.index', compact('reports'));
    }

    // Reports are generally created by users publicly, so no Create method for Admin usually.
    
    public function show(Report $report)
    {
        return view('admin.reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        return view('admin.reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed_valid,reviewed_invalid,resolved',
            'admin_notes' => 'nullable|string',
        ]);

        $report->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by_admin_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.reports.index')->with('success', 'Report status updated.');
    }

    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted.');
    }
}
