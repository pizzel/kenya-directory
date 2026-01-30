<?php
namespace App\Policies;
use App\Models\Event;
use App\Models\User;

class EventPolicy {
    public function before(User $user, string $ability): bool|null {
        if ($user->isAdmin()) return true; // Admins can do anything
        return null;
    }
    public function viewAny(User $user): bool { return $user->isBusinessOwner(); } // Owner can see their list
    public function view(User $user, Event $event): bool { return (int) $user->id === (int) $event->user_id; }
    public function create(User $user): bool { return $user->isBusinessOwner(); }
    public function update(User $user, Event $event): bool { return (int) $user->id === (int) $event->user_id; }
    public function delete(User $user, Event $event): bool { return (int) $user->id === (int) $event->user_id; }
    // public function restore(User $user, Event $event): bool { return $user->id === $event->user_id; }
    // public function forceDelete(User $user, Event $event): bool { return $user->id === $event->user_id; }
}