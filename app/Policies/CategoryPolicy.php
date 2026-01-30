<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) { // Super admin can do anything
            return true;
        }
        return null; // Let other checks proceed for other roles
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isEditor(); // Editors can view the list of categories
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->isEditor(); // Editors can view a specific category
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isEditor(); // Editors can create new categories
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->isEditor(); // Editors can update categories
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->isEditor(); // Editors can delete categories
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Category $category): bool
    // {
    //     return $user->isEditor();
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Category $category): bool
    // {
    //     return $user->isEditor();
    // }
}