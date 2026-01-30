<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'day_of_week', 'open_time', 'close_time', 'is_closed_all_day', 'notes'
    ];

    protected $casts = [
        'is_closed_all_day' => 'boolean',
    ];

    public $timestamps = false; // Typically schedules don't need created_at/updated_at

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}