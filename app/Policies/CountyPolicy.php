<?php

namespace App\Policies;

use App\Models\County;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CountyPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) { return true; }
        return null;
    }

    public function viewAny(User $user): bool { return false; } // Editors cannot see list, Admins by 'before'
    public function view(User $user, County $county): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, County $county): bool { return false; }
    public function delete(User $user, County $county): bool { return false; }
    // public function restore(User $user, County $county): bool { return false; }
    // public function forceDelete(User $user, County $county): bool { return false; }
}