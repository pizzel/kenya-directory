<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'author_name',
        'author_url',
        'profile_photo_url',
        'rating',
        'text',
        'relative_time_description',
        'time'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}