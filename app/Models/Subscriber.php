<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * FIXED: Added 'email' and 'is_active' to allow the subscription form to work.
     */
    protected $fillable = [
        'email',
        'is_active',
    ];
}