<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'business_id', 'status']; // Add 'status'

    public function user() { return $this->belongsTo(User::class); }
    public function business() { return $this->belongsTo(Business::class); }
}