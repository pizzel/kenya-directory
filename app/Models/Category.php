<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // These are the attributes that are mass assignable
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'icon_class'
    ];

    // Relationship: A Category can have a parent Category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relationship: A Category can have many child Categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relationship: A Category can belong to many Businesses
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_category');
        // Assuming your pivot table is named 'business_category'
        // And has 'business_id' and 'category_id' columns
    }
}