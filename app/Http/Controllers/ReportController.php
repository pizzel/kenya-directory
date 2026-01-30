<?php
namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Business;
use App\Models\Event; // Import Event model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // One of these must be present
            'business_id' => 'nullable|required_without:event_id|exists:businesses,id',
            'event_id' => 'nullable|required_without:business_id|exists:events,id',
            'report_reason' => ['required', Rule::in([
                'scam_fraud', 'political_ad', 'adult_content', 'restricted_items',
                'violence_hate', 'bullying_unwanted', 'intellectual_property',
                'self_harm', 'false_info', 'other'
            ])],
            'details' => 'nullable|string|max:150',
        ]);

        $userId = Auth::id();
        $reportableId = $request->input('business_id') ?? $request->input('event_id');
        $reportableType = $request->input('business_id') ? Business::class : Event::class;

        // Check for existing report by this user/IP for this specific item
        $query = Report::where($reportableType === Business::class ? 'business_id' : 'event_id', $reportableId);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('ip_address', $request->ip());
        }

        if ($query->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported this item. Our team will review it.'
            ], 422);
        }

        Report::create([
            'business_id' => $request->input('business_id'),
            'event_id' => $request->input('event_id'),
            'user_id' => $userId,
            'report_reason' => $validatedData['report_reason'],
            'details' => $validatedData['details'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending',
        ]);

        // Auto-close logic (now needs to handle events too)
        $reportThreshold = 1;
        if ($reportableType === Business::class) {
            $item = Business::find($reportableId);
            $distinctReportersCount = Report::where('business_id', $item->id)->where('status', 'pending')
                                         ->distinct($userId ? 'user_id' : 'ip_address')->count($userId ? 'user_id' : 'ip_address');
            if ($item && $item->status === 'active' && $distinctReportersCount >= $reportThreshold) {
                $item->status = 'closed_by_reports';
                $item->save();
                Log::info("Business ID {$item->id} auto-closed due to reports.");
            }
        } elseif ($reportableType === Event::class) {
            $item = Event::find($reportableId);
            $distinctReportersCount = Report::where('event_id', $item->id)->where('status', 'pending')
                                         ->distinct($userId ? 'user_id' : 'ip_address')->count($userId ? 'user_id' : 'ip_address');
            if ($item && $item->status === 'active' && $distinctReportersCount >= $reportThreshold) {
                $item->status = 'cancelled'; // Or 'closed_by_reports' if you add that status to events
                $item->save();
                Log::info("Event ID {$item->id} auto-cancelled due to reports.");
                // TODO: You might need a 'closed_by_reports' status for events too.
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully. Thank you!'
        ]);
    }
}