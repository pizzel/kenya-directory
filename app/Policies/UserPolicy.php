<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     * Only Super Admins can manage other users.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        // For all other users (including editors), if the ability is not 'view' for oneself, deny.
        // Or simply let specific methods return false for editors.
        return null; // Let other policy methods define permissions
    }

    /**
     * Determine whether the user can view any models.
     * Editors might see a list, but can't act. Super admin can.
     */
    public function viewAny(User $user): bool
    {
        // Editors could potentially view the list but not perform actions.
        // However, given your requirement they "don't have access to create users",
        // it might be better to restrict viewAny as well for non-admins.
        // Let's assume for now only admins see the full user list in Filament.
        return false; // Editors cannot see the user list. Admins covered by before().
                      // If UserResource is hidden from editors, this won't even be checked for them.
    }

    /**
     * Determine whether the user can view the model.
     * A user can view their own profile. Admins can view any. Editors can't view other profiles.
     */
    public function view(User $user, User $model): bool // $model is the user being viewed
    {
        // Admins covered by before().
        // A user can view their own profile. Editors cannot view other user profiles through this policy.
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Only Super Admins (via 'before' method) can create users.
    }

    /**
     * Determine whether the user can update the model.
     * A user can update their own profile. Admins can update any. Editors cannot update others.
     */
    public function update(User $user, User $model): bool // $model is the user being updated
    {
         // Admins covered by before().
         // Editors cannot update users (even themselves through a UserResource).
         // Users update their own profile via ProfileController, not UserResource.
        return false; // Let profile specific policies/routes handle self-update.
                      // Or, if UserResource IS for self-update: return $user->id === $model->id;
                      // But usually UserResource is for admins managing ALL users.
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Prevent deleting oneself if you are the last admin (add more complex logic if needed)
        if ($user->id === $model->id && $user->isAdmin()) {
            // Add logic here to check if they are the only admin, if so, deny.
            // For simplicity now, allowing admin to delete others.
        }
        return false; // Only Super Admins (via 'before' method) can delete users.
    }

    // public function restore(User $user, User $model): bool { return false; }
    // public function forceDelete(User $user, User $model): bool { return false; }
}