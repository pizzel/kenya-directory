<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'report_reason',
        'details',
        'ip_address',
        'user_agent',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
        'admin_notes',
		'event_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
	

    public function user() // The user who made the report
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewedByAdmin() // The admin who reviewed the report
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
	public function event() {
    return $this->belongsTo(Event::class);
}
}