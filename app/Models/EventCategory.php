<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model {
    use HasFactory;
    protected $fillable = ['name', 'slug', 'icon_class'/*, 'parent_id'*/];
    public $timestamps = true; // Or false if you don't need them

    public function events() {
        return $this->belongsToMany(Event::class, 'event_event_category');
    }
    // public function parent() { return $this->belongsTo(EventCategory::class, 'parent_id'); }
    // public function children() { return $this->hasMany(EventCategory::class, 'parent_id'); }
}