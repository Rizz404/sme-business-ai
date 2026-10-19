<?php

namespace App\Modules\Booking\Queries;

use App\Models\Business;
use App\Models\ServiceArea;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * No slot is free in the requested area and slot: find the other areas that
 * can host the same group at the same time, and the other slots of the same
 * area that day.
 */
class FindAlternatives
{
    public function __construct(private readonly SlotAvailability $availability) {}

    /**
     * @return Collection<int, array{type: string, slug?: string, time_slot?: string, name: string, fee?: float}>
     */
    public function handle(Business $business, ServiceArea $requested, CarbonImmutable $date, string $timeSlot, int $attendees, string $locale): Collection
    {
        $otherAreas = $business->serviceAreas()
            ->where('is_active', true)
            ->whereKeyNot($requested->id)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (ServiceArea $area) => $this->availability->fitsAttendees($area, $attendees))
            ->map(function (ServiceArea $area) use ($date, $timeSlot, $locale) {
                $quote = $this->availability->quote($area, $date, $timeSlot);

                return $quote ? [
                    'type' => 'area',
                    'slug' => $area->slug,
                    'name' => $area->translatedName($locale),
                    'fee' => $quote['fee'],
                ] : null;
            })
            ->filter()
            ->values();

        $otherSlots = collect($this->availability->availableSlots($requested, $date))
            ->reject(fn (string $slot) => $slot === $timeSlot)
            ->map(fn (string $slot) => [
                'type' => 'slot',
                'time_slot' => $slot,
                'name' => SlotAvailability::slotRange($slot),
            ])
            ->values();

        return $otherSlots->merge($otherAreas)->values();
    }
}
