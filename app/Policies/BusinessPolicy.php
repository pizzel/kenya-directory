<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;
//use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Attributes\WithTrashed;
use Illuminate\Database\Eloquent\Model;


class BusinessPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admins can do anything with businesses.
     */
    public function before(User $user, string $ability): bool|null
    {
        // This is perfect. Super admins can do anything.
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Let other policy methods decide for non-super-admins
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Editors and Admins can view the list of all businesses.
        // Admin is covered by before(), so this is for the editor.
        return $user->isEditor();
    }

    /**
     * Determine whether the user can view the model.
     * THIS IS THE MAIN FIX FOR THE EMPTY TABLE.
     */
    public function view(User $user, #[WithTrashed] Business $business): bool // <<< FIX #1: Added #[WithTrashed]
    {
        // Editors can view ANY business, whether it is active or trashed.
        // Admin is covered by before().
        if ($user->isEditor()) {
            return true;
        }

        // Business owners can view their OWN business, but only if it's NOT trashed.
        if ($user->id === $business->user_id && !$business->trashed()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
     public function create(User $user): bool
    {
        // Explicitly allow Admins OR Business Owners to create a new business.
        // This makes the permission check direct and self-contained,
        // avoiding potential conflicts from other policy methods like viewAny.
        return $user->isAdmin() || $user->isBusinessOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Business $business): bool
    {
        if ($user->isEditor()) { return true; }

        return (int) $user->id === (int) $business->user_id;
    }

    /**
     * Determine whether the user can soft delete the model.
     */
    public function delete(User $user, Business $business): bool
    {
        // Let's say Editors and Owners can "Archive" (soft delete) a business.
        if ($user->isEditor()) { return true; }

        return (int) $user->id === (int) $business->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, #[WithTrashed] Business $business): bool // <<< FIX #2: Added #[WithTrashed]
    {
        // Editors can restore an archived business.
        // Admin is covered by before().
        return $user->isEditor();
    }

    /**
    * Determine whether the user can permanently delete the model.
    * THIS IS THE FIX FOR YOUR FORCE DELETE ACTION.
    */
    public function forceDelete(User $user, #[WithTrashed] Business $business): bool // <<< FIX #3: Added #[WithTrashed]
    {
        // Only Super Admins can permanently delete.
        // The before() method already gives them this power. We set this to false
        // to explicitly PREVENT editors from being able to force delete.
        return false;
    }

    /**
     * Determine whether the user can verify or unverify the business.
     */
    public function verify(User $user, Business $business): bool
    {
        return $user->isEditor();
    }

    /**
     * Determine whether the user can change the status of the business.
     */
    public function changeStatus(User $user, Business $business): bool
    {
        return $user->isEditor();
    }

    /**
     * Determine whether the user can set a business back to 'active'.
     */
    public function setActive(User $user, Business $business): bool
    {
        // Explicitly deny editors this specific action. Only Admins can.
        return false;
    }
}