<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add if using soft deletes

class EventReview extends Model {
    use HasFactory, SoftDeletes; // Add SoftDeletes if applicable
    protected $fillable = ['event_id', 'user_id', 'rating', 'comment'];
    // No 'is_approved' in fillable

    public function event() { return $this->belongsTo(Event::class); }
    public function user() { return $this->belongsTo(User::class); }
}