<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug']; // Add 'slug' here
    public $timestamps = false;

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}