<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Correct base class
// use Illuminate\Database\Eloquent\Relations\Pivot; // If treating strictly as pivot

class EventWishlist extends Model // Can extend Model directly
{
    use HasFactory;

    protected $table = 'event_wishlists'; // Explicitly define table name

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}