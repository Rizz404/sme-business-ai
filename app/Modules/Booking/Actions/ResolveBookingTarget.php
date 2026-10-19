<?php

namespace App\Modules\Booking\Actions;

use App\Models\Business;
use App\Models\ServiceArea;
use App\Modules\Booking\Support\SlotAvailability;
use App\Modules\Booking\Validation\BookingRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Turns the guest's already well-formed request into the service area and
 * date it names, or tells them in their own language what does not fit.
 */
class ResolveBookingTarget
{
    public function __construct(private readonly SlotAvailability $availability) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: ServiceArea, 1: CarbonImmutable}
     *
     * @throws ValidationException
     */
    public function handle(Business $business, array $data, string $locale): array
    {
        $area = $business->serviceAreas()->where('is_active', true)->where('slug', $data['service_area_slug'])->first();

        if (! $area) {
            throw ValidationException::withMessages(['service_area_slug' => BookingRules::message('area_unknown', $locale)]);
        }

        $date = CarbonImmutable::parse($data['booking_date'], $business->timezone)->startOfDay();
        $today = CarbonImmutable::now($business->timezone)->startOfDay();

        if ($today->diffInDays($date) > SlotAvailability::MAX_ADVANCE_DAYS) {
            throw ValidationException::withMessages([
                'booking_date' => BookingRules::message('date_far', $locale, ['max' => (string) SlotAvailability::MAX_ADVANCE_DAYS]),
            ]);
        }

        if (! in_array($data['time_slot'], $this->availability->slotsFor($business), true)) {
            throw ValidationException::withMessages(['time_slot' => BookingRules::message('slot_unknown', $locale)]);
        }

        if ($this->availability->isSlotInPast($business, $date, $data['time_slot'])) {
            throw ValidationException::withMessages(['time_slot' => BookingRules::message('slot_past', $locale)]);
        }

        if (! $this->availability->fitsAttendees($area, (int) $data['attendees'])) {
            throw ValidationException::withMessages(['attendees' => BookingRules::message('capacity', $locale)]);
        }

        return [$area, $date];
    }
}
