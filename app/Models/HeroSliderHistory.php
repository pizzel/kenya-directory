<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <<< IMPORT
class HeroSliderHistory extends Model
{

	use HasFactory, SoftDeletes; // <<< USE TRAIT
    protected $table = 'hero_slider_histories'; // Explicitly define if needed, though Laravel should infer it

    protected $fillable = [
        'business_id',
        'admin_id',
        'activated_at',
        'set_to_expire_at', // Ensure this matches your migration
        'amount_paid',
        'payment_reference',
        'package_name',
        'notes',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'set_to_expire_at' => 'datetime', // Ensure this matches your migration
        'amount_paid' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
	
	
	 /**
     * Check how many other distinct businesses have active placements
     * that overlap with the given time range.
     *
     * @param Carbon $newStartTime
     * @param Carbon $newEndTime
     * @param int|null $excludePlacementId (ID of the current placement being edited, if any)
     * @return int
     */
    public static function countOverlappingDistinctBusinesses(Carbon $newStartTime, Carbon $newEndTime, ?int $excludePlacementId = null): int
    {
        $query = self::query() // Start query on HeroSliderHistory model
            ->where(function ($q) use ($newStartTime, $newEndTime) {
                // Check for overlap:
                // Existing placement starts before new one ends
                $q->where('activated_at', '<', $newEndTime)
                // And existing placement ends after new one starts
                  ->where('set_to_expire_at', '>', $newStartTime);
            });

        if ($excludePlacementId) {
            $query->where('id', '!=', $excludePlacementId); // Exclude the current record if editing
        }

        // We only want to count distinct businesses
        return $query->distinct('business_id')->count('business_id');
    }

	
}