<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // <<< ADD THIS IMPORT

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Store a newly created review in storage or update an existing one.
     * NOW HANDLES BOTH AJAX AND STANDARD FORM REQUESTS.
     */
    public function store(Request $request, Business $business)
    {
        try {
            $this->authorize('create', [Review::class, $business]);

            $validatedData = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:4|max:1000',
            ]);

            // Use updateOrCreate to simplify the create/update logic.
            // It finds a record based on the first array, or creates it with the merged data.
            $review = Review::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'business_id' => $business->id,
                ],
                [
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment'],
                ]
            );

            // After creating or updating, update the business's average rating
            if (method_exists($business, 'updateAverageRating')) {
                $business->updateAverageRating();
            }

            $successMessage = $review->wasRecentlyCreated ? 
                'Thank you for your review! It has been submitted.' : 
                'Your review has been updated successfully!';

            // --- START: NEW AJAX RESPONSE LOGIC ---
            if ($request->ajax() || $request->wantsJson()) {
                // Eager load the user (author) relationship for the new/updated review
                $review->load('user');

                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    // We render the new comment on the server and send back the HTML
                    // This uses the exact same partial as the event reviews.
                    'html' => view('partials._comment-item', ['review' => $review])->render()
                ]);
            }
            // --- END: NEW AJAX RESPONSE LOGIC ---

            return back()->with('success', $successMessage);

        } catch (ValidationException $e) {
            // Handle validation errors specifically for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            // For standard requests, Laravel will handle the redirect back with errors automatically.
            throw $e;
        }
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Request $request, Review $review)
    {
        // This method is fine as is, since deleting will likely always involve a page interaction
        // or be handled by an admin panel. No changes needed here.
        $this->authorize('delete', $review);
        $business = $review->business;
        $review->delete();
        if ($business && method_exists($business, 'updateAverageRating')) {
            $business->updateAverageRating();
        }
        return back()->with('success', 'Review removed successfully.');
    }
}