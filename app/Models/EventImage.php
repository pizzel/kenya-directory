<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // If you added soft deletes to event_images table
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class EventImage extends Model {
    use HasFactory, SoftDeletes; // Add SoftDeletes if your migration has it
    protected $fillable = ['event_id', 'file_path', 'caption', 'is_main_event_image', 'order'];
    protected $casts = ['is_main_event_image' => 'boolean'];
    public function event() { return $this->belongsTo(Event::class); }
    public function getUrlAttribute(): ?string {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return asset('images/placeholder-event.jpg'); // Default placeholder
    }
}