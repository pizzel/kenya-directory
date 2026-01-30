<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'business_id', 'rating', 'comment', //'is_approved'
    ];

    // protected $casts = [
        // 'is_approved' => 'boolean',
    // ];

    public function user() // The user who wrote the review
    {
        return $this->belongsTo(User::class);
    }

    public function business() // The business being reviewed
    {
        return $this->belongsTo(Business::class);
    }
}