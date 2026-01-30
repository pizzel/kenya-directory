<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule; // Ensures we can get all form data
use App\Models\HeroSliderHistory; // Your model for hero placements
use Carbon\Carbon;
//use Illuminate\Support\Facades\Log; // Optional: for debugging complex validation

class MaxConcurrentHeroPlacements implements ValidationRule, DataAwareRule
{
    protected array $data = []; // Will be populated by Laravel
    protected ?int $currentEditingPlacementId; // ID of the placement being edited (null if creating)
    protected int $businessIdForPlacement;     // The business_id this placement belongs to
    protected int $maxGlobalSlots = 10;        // Your defined global slot limit

    /**
     * Create a new rule instance.
     *
     * @param int $businessIdForPlacement The ID of the business this placement is for.
     * @param int|null $currentEditingPlacementId The ID of the HeroSliderHistory record if editing, null if creating.
     * @return void
     */
    public function __construct(int $businessIdForPlacement, ?int $currentEditingPlacementId = null)
    {
        $this->businessIdForPlacement = $businessIdForPlacement;
        $this->currentEditingPlacementId = $currentEditingPlacementId;
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extract the relevant form data for activated_at, duration_value, duration_unit
        // This needs to correctly get data whether it's from a main form or an action modal
        $formData = $this->data; // Default to top-level
        if (isset($this->data['mountedTableActionsData'][0]) && is_array($this->data['mountedTableActionsData'][0])) {
            $formData = $this->data['mountedTableActionsData'][0]; // For action modals in Filament tables
        }
        // Sometimes Filament resource forms pass data directly under $this->data (when using $record in edit)
        // or $this->data['data'] when using Livewire properties.
        // Let's make it more robust by checking common paths or if data is directly in $this->data
        elseif (isset($this->data['data']) && is_array($this->data['data']) && isset($this->data['data']['activated_at'])) {
            $formData = $this->data['data'];
        }


        $activatedAtInput = $formData['activated_at'] ?? null;
        $durationValue = $formData['duration_value'] ?? null;
        $durationUnit = $formData['duration_unit'] ?? null;

        if (!$this->businessIdForPlacement) {
            $fail('Business context is missing for overlap check. Please contact support.'); // Should not happen
            return;
        }

        if (!$activatedAtInput || $durationValue === null || !$durationUnit) {
            // This validation should ideally be handled by 'required' rules on individual fields
            // This rule focuses on the overlap after basic data is present.
            // If these are empty, the calculated_expiry will be null, and that's fine for this rule not to trigger.
            // If they are *required*, other validation rules should catch it.
            return;
        }

        try {
            $appTimezone = config('app.timezone');
            // When Filament's DateTimePicker provides the date, it's already a string in the app's timezone.
            $proposedStart = Carbon::parse($activatedAtInput, $appTimezone);
            $proposedEnd = $proposedStart->copy();

            if ($durationUnit === 'hours') {
                $proposedEnd->addHours((int)$durationValue);
            } elseif ($durationUnit === 'days') {
                $proposedEnd->addDays((int)$durationValue);
            } else {
                $fail('Invalid duration unit selected.'); // Should be caught by Select options
                return;
            }
        } catch (\Exception $e) {
            // Log::error('Date parsing/calculation error in MaxConcurrentHeroPlacements rule: ' . $e->getMessage(), ['data' => $formData]);
            $fail("Invalid date or duration format. Please ensure dates and times are correct.");
            return;
        }

        // --- Check 1: Self-overlap for THIS business ---
        // (Ensures this business doesn't have another of its own placements overlapping the proposed time)
        $selfOverlapQuery = HeroSliderHistory::where('business_id', $this->businessIdForPlacement)
            ->where(function ($query) use ($proposedStart, $proposedEnd) {
                $query->where('activated_at', '<', $proposedEnd->toDateTimeString())
                      ->where('set_to_expire_at', '>', $proposedStart->toDateTimeString());
            });

        if ($this->currentEditingPlacementId) {
            $selfOverlapQuery->where('id', '!=', $this->currentEditingPlacementId); // Exclude the record being edited
        }

        if ($selfOverlapQuery->exists()) {
            $fail('This feature period overlaps with another existing placement for this same business.');
            return; // Stop if self-overlap
        }

        // --- Check 2: Global Concurrent Slot Limit (across ALL businesses) ---
        // Query for all placements from OTHER businesses that would overlap with the proposed new period
        $otherBusinessesOverlappingQuery = HeroSliderHistory::query()
            ->where('business_id', '!=', $this->businessIdForPlacement) // Exclude placements from the current business
            ->where(function ($query) use ($proposedStart, $proposedEnd) {
                $query->where('activated_at', '<', $proposedEnd->toDateTimeString())
                      ->where('set_to_expire_at', '>', $proposedStart->toDateTimeString());
            });

        // If we are editing an existing placement, we must also exclude its *original* slot
        // from the count of "others", IF its original slot was different from the new proposed slot.
        // However, the line `->where('business_id', '!=', $this->businessIdForPlacement)` already handles
        // ensuring we are only counting *other* businesses.

        // Count how many *other distinct businesses* have an active placement during the proposed time.
        $countOfOtherDistinctBusinessesInSlot = $otherBusinessesOverlappingQuery->distinct()->count('business_id');

        // If the number of *other* businesses already occupying slots is equal to or greater than
        // the max allowed slots, then our current business cannot be added.
        if ($countOfOtherDistinctBusinessesInSlot >= $this->maxGlobalSlots) {
            $fail("All {$this->maxGlobalSlots} hero slider slots are booked by other businesses during the proposed period. Please choose a different time or duration.");
            return;
        }

        // If we reach here, it means:
        // 1. There's no self-overlap for the current business.
        // 2. The number of *other* distinct businesses in the proposed slot is less than maxGlobalSlots,
        //    so there's room for *this* business's placement.
    }
}