<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TagPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) { return true; }
        return null;
    }

    public function viewAny(User $user): bool { return $user->isEditor(); }
    public function view(User $user, Tag $tag): bool { return $user->isEditor(); }
    public function create(User $user): bool { return $user->isEditor(); }
    public function update(User $user, Tag $tag): bool { return $user->isEditor(); }
    public function delete(User $user, Tag $tag): bool { return $user->isEditor(); }
    // public function restore(User $user, Tag $tag): bool { return $user->isEditor(); }
    // public function forceDelete(User $user, Tag $tag): bool { return $user->isEditor(); }
}