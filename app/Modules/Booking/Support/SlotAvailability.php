<?php

namespace App\Modules\Booking\Support;

use App\Models\Business;
use App\Models\ServiceArea;
use App\Models\SlotInventory;
use Carbon\CarbonImmutable;

/**
 * The single place where slot availability and slot quotes are computed.
 * Both the AI Business Assistant tools and the guest-facing booking wizard go
 * through here, so they can never disagree.
 */
class SlotAvailability
{
    /**
     * How far ahead a slot can be requested.
     */
    public const MAX_ADVANCE_DAYS = 60;

    /**
     * Every slot is a fixed two-hour appointment.
     */
    public const SLOT_MINUTES = 120;

    /**
     * Whether the attendee count suits the service area.
     */
    public function fitsAttendees(ServiceArea $area, int $attendees): bool
    {
        return $attendees >= max(1, $area->min_attendees) && $attendees <= $area->max_attendees;
    }

    /**
     * The slot start times the business takes bookings for, earliest first.
     *
     * @return list<string>
     */
    public function slotsFor(Business $business): array
    {
        return SlotInventory::query()
            ->whereIn('service_area_id', $business->serviceAreas()->where('is_active', true)->select('id'))
            ->distinct()
            ->orderBy('time_slot')
            ->pluck('time_slot')
            ->all();
    }

    /**
     * "11:00–13:00" for a slot that starts at 11:00.
     */
    public static function slotRange(string $slot): string
    {
        $start = CarbonImmutable::createFromFormat('H:i', $slot);

        return $start->format('H:i').'–'.$start->addMinutes(self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Whether the slot has already begun in the business's own time zone.
     */
    public function isSlotInPast(Business $business, CarbonImmutable $date, string $slot): bool
    {
        $start = CarbonImmutable::parse($date->toDateString().' '.$slot, $business->timezone);

        return $start->lessThanOrEqualTo(CarbonImmutable::now($business->timezone));
    }

    /**
     * Checks one slot. Returns null if there is no free slot in it, which is
     * the one place availability truth comes from, never the model.
     *
     * @return array{date: string, time_slot: string, time_range: string, open_units: int, fee: float, minimum_order: ?float}|null
     */
    public function quote(ServiceArea $area, CarbonImmutable $date, string $slot, bool $lockForUpdate = false): ?array
    {
        $query = SlotInventory::where('service_area_id', $area->id)
            ->whereDate('booking_date', $date->toDateString())
            ->where('time_slot', $slot);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (! $inventory || ! $inventory->isAvailable()) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'time_slot' => $slot,
            'time_range' => self::slotRange($slot),
            'open_units' => $inventory->openUnits(),
            'fee' => (float) $area->booking_fee,
            'minimum_order' => $area->minimum_order !== null ? (float) $area->minimum_order : null,
        ];
    }

    /**
     * Every slot of the day that still has a free slot and has not started.
     *
     * @return list<string>
     */
    public function availableSlots(ServiceArea $area, CarbonImmutable $date): array
    {
        $business = $area->business;

        return SlotInventory::where('service_area_id', $area->id)
            ->whereDate('booking_date', $date->toDateString())
            ->orderBy('time_slot')
            ->get()
            ->filter(fn (SlotInventory $row) => $row->isAvailable() && ! $this->isSlotInPast($business, $date, $row->time_slot))
            ->pluck('time_slot')
            ->values()
            ->all();
    }
}
