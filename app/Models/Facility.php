<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon_class', 'slug'];
    public $timestamps = false;

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_facility');
    }
	 public function getRouteKeyName()
    {
        return 'slug'; // Tells Laravel to use 'slug' for route model binding
    }
}