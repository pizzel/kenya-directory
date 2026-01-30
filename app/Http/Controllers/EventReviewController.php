<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException; // Import for exception handling

class EventReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'isNotBlocked'])->only(['store', 'destroy']);
    }

    public function store(Request $request, Event $event)
    {
        try {
            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
            ]);

            $eventReview = EventReview::updateOrCreate(
                ['user_id' => Auth::id(), 'event_id' => $event->id],
                ['rating' => $validatedData['rating'], 'comment' => $validatedData['comment']]
            );

            if (method_exists($event, 'updateAverageRatingAndReviewCount')) {
                $event->updateAverageRatingAndReviewCount();
            }

            // --- START: NEW AJAX RESPONSE LOGIC ---
            if ($request->ajax() || $request->wantsJson()) {
                // Eager load the user (author) relationship for the new review
                $eventReview->load('user');

                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for your feedback! Your review has been submitted.',
                    // We render the new comment on the server and send back the HTML
                    'html' => view('partials._comment-item', ['review' => $eventReview])->render()
                ]);
            }
            // --- END: NEW AJAX RESPONSE LOGIC ---

            return back()->with('success', 'Thank you for your feedback! Your review has been submitted.');

        } catch (ValidationException $e) {
            // Handle validation errors specifically for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            // For standard requests, it will redirect back with errors automatically.
            throw $e;
        }
    }

    public function destroy(EventReview $eventReview)
    {
        // ... your destroy method is fine as is, since it already redirects back ...
        $user = Auth::user();
        if (!($user->isAdmin() || $user->isEditor() || $user->id === $eventReview->user_id)) {
            abort(403, 'Unauthorized to delete this review.');
        }
        $event = $eventReview->event;
        $eventReview->delete();
        if (method_exists($event, 'updateAverageRatingAndReviewCount')) {
            $event->updateAverageRatingAndReviewCount();
        }
        return back()->with('success', 'Event review removed.');
    }
}