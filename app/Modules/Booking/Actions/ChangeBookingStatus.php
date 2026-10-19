<?php

namespace App\Modules\Booking\Actions;

use App\Models\Booking;
use App\Models\SlotInventory;

/**
 * Confirms or cancels a booking request. Cancelling gives the held slot
 * back to the inventory, once.
 */
class ChangeBookingStatus
{
    public function handle(Booking $booking, string $status): Booking
    {
        if ($status === Booking::STATUS_CANCELLED && $booking->status !== Booking::STATUS_CANCELLED) {
            $this->releaseUnit($booking);
        }

        $booking->update(['status' => $status]);

        return $booking;
    }

    /**
     * Gives the slot held by a booking back to the inventory.
     */
    public function releaseUnit(Booking $booking): void
    {
        SlotInventory::where('service_area_id', $booking->service_area_id)
            ->whereDate('booking_date', $booking->booking_date->toDateString())
            ->where('time_slot', $booking->time_slot)
            ->decrement('reserved_units');
    }
}
