<?php

namespace App\Policies;

use App\Models\HeroSliderHistory;
use App\Models\User;
use Carbon\Carbon; // Import Carbon for date comparisons

class HeroSliderHistoryPolicy
{
    // use HandlesAuthorization; // Optional trait

    /**
     * Perform pre-authorization checks.
     * If user is a Super Admin, grant all permissions for HeroSliderHistory records.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) { // Assumes isAdmin() helper exists on your User model
            return true;
        }
        return null; // Defer to specific ability methods for other roles (like Editor)
    }

    /**
     * Determine whether the user can view any models.
     * This controls the visibility of the "Hero Slider Schedule" in navigation.
     */
    public function viewAny(User $user): bool
    {
        // Allow Editors to view the list. Admins are already covered by the 'before' method.
        return $user->isEditor(); // Assumes isEditor() helper exists on your User model
    }

    /**
     * Determine whether the user can view the model.
     * (e.g., when clicking "View" on a specific history record)
     */
    public function view(User $user, HeroSliderHistory $heroSliderHistory): bool
    {
        // Allow Editors to view individual records. Admins covered by 'before'.
        return $user->isEditor();
    }

    /**
     * Determine whether the user can create models.
     * Creation is disabled in HeroSliderHistoryResource itself via canCreate().
     * This policy method should align with that.
     */
    public function create(User $user): bool
    {
        return false; // No direct creation from this resource's main page
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HeroSliderHistory $heroSliderHistory): bool
    {
        // Admins can always update (covered by 'before' method).
        // Editors can only update if the placement has not expired.
        if ($user->isEditor()) {
            // Ensure set_to_expire_at is a Carbon instance
            $expiresAt = ($heroSliderHistory->set_to_expire_at instanceof Carbon)
                ? $heroSliderHistory->set_to_expire_at
                : Carbon::parse($heroSliderHistory->set_to_expire_at);

            if ($expiresAt->isPast()) {
                return false; // Editor cannot edit expired placements
            }
            return true; // Editor can edit active or upcoming placements
        }
        return false; // Other roles (like business_owner or user) cannot update history
    }


    /**
     * Determine whether the user can delete the model.
     * (Deleting historical records is sensitive)
     */
    public function delete(User $user, HeroSliderHistory $heroSliderHistory): bool
    {
        // Typically, only Super Admins should delete history records.
        // Since Admins are covered by 'before', this will effectively be false for Editors.
        return false; // Editors cannot delete, Admins can (due to 'before').
    }

    /**
     * Determine whether the user can restore the model (if using SoftDeletes on HeroSliderHistory).
     */
    // public function restore(User $user, HeroSliderHistory $heroSliderHistory): bool
    // {
    //     return false; // Example: Editors cannot restore, Admins can (due to 'before')
    // }

    /**
     * Determine whether the user can permanently delete the model (if using SoftDeletes).
     */
    // public function forceDelete(User $user, HeroSliderHistory $heroSliderHistory): bool
    // {
    //     return false; // Example: Editors cannot force delete, Admins can (due to 'before')
    // }
}