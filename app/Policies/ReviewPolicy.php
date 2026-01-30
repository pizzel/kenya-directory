<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Business; // Import Business if needed for context
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Admins can do anything.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Let other policy methods decide
    }

    /**
     * Determine whether the user can view any models.
     * (Not typically used directly for reviews, but good to have)
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can see lists of reviews (if they are public)
    }

    /**
     * Determine whether the user can view the model.
     * (Not typically used directly, as reviews are shown with businesses)
     */
    public function view(User $user, Review $review): bool
    {
        return true; // Anyone can see an individual review if it's part of a public listing
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Business $business): bool // Pass Business context for creation
    {
        // Any authenticated (and verified) user can leave a review,
        // unless you have other restrictions (e.g., one review per user per business)
        //return Auth::check(); // Or just true if middleware already handles auth
		 if ($user->blocked_at !== null) return false; // Extra check
			if ($business->user_id === $user->id) return false;
			return true;
    }

    /**
     * Determine whether the user can update the model.
     * (Generally, users can't update reviews, they delete and re-submit. Admins/owners might.)
     */
    public function update(User $user, Review $review): bool
    {
        // Example: Only the review author OR admin can update.
        // Business owners usually don't update user reviews.
        return $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        // User can delete their own review
        // OR Business owner can delete any review on their business
        // OR Admin can delete any review (covered by 'before' method)
        $business = $review->business; // Get the business the review belongs to

       // return $user->id === $review->user_id || // Is the author?
          //     ($user->isBusinessOwner() && $user->id === $business->user_id); // Is the owner of the business?
		  return $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Review $review): bool
    // {
    //     return $user->id === $review->user_id || ($user->isBusinessOwner() && $user->id === $review->business->user_id);
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Review $review): bool
    // {
    //     return $user->id === $review->user_id || ($user->isBusinessOwner() && $user->id === $review->business->user_id);
    // }
}