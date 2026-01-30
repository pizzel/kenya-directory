<?php
namespace App\Policies;

use App\Models\EventReview;
use App\Models\User;
use App\Models\Event; // To check event ownership if needed

class EventReviewPolicy
{
    /**
     * Super Admins can do anything.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, EventReview $eventReview): bool { return true; }

    /**
     * Determine whether the user can create models.
     * Any authenticated user can create a review on an event they are not organizing.
     */
    public function create(User $user, Event $event): bool // Context of the event being reviewed
    {
        // Prevent business owner from reviewing their own event
        if ($user->isBusinessOwner() && $event->user_id === $user->id) {
            return false;
        }
        return true; // All other authenticated users can
    }

    public function update(User $user, EventReview $eventReview): bool
    {
        // Generally, users don't edit reviews. Admins/Editors might correct typos.
        return $user->isEditor() || $user->id === $eventReview->user_id; // Author or Editor
    }

    public function delete(User $user, EventReview $eventReview): bool
    {
        // User can delete their own review.
        // Editors can delete any review.
        // Admins are covered by 'before'.
        // Business Owners CANNOT delete reviews on their events.
        return $user->id === $eventReview->user_id || $user->isEditor();
    }

    // Define restore and forceDelete if EventReview model uses SoftDeletes
    // public function restore(User $user, EventReview $eventReview): bool { return $user->isEditor(); }
    // public function forceDelete(User $user, EventReview $eventReview): bool { return $user->isAdmin(); }
}