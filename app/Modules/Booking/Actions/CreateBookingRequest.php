<?php

namespace App\Modules\Booking\Actions;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\ServiceArea;
use App\Models\SlotInventory;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Creates a pending booking request and holds one slot for it. Both the
 * booking wizard and the AI Business Assistant's booking tool end up here.
 */
class CreateBookingRequest
{
    public function __construct(private readonly SlotAvailability $availability) {}

    /**
     * Returns null when no slot is free in that slot any more.
     *
     * @param  array{booking_date: CarbonImmutable, time_slot: string, attendees: int, purpose?: ?string, customer_name: string, customer_email?: ?string, customer_phone?: ?string, contact_type?: ?string, notes?: ?string}  $data
     */
    public function handle(Business $business, ServiceArea $area, array $data, ?Conversation $conversation = null): ?Booking
    {
        return DB::transaction(function () use ($business, $area, $data, $conversation) {
            $quote = $this->availability->quote($area, $data['booking_date'], $data['time_slot'], lockForUpdate: true);

            if (! $quote) {
                return null;
            }

            $booking = Booking::create([
                'reference' => Booking::generateReference(),
                'business_id' => $business->id,
                'service_area_id' => $area->id,
                'conversation_id' => $conversation?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'contact_type' => $data['contact_type'] ?? null,
                'booking_date' => $data['booking_date']->toDateString(),
                'time_slot' => $data['time_slot'],
                'attendees' => $data['attendees'],
                'purpose' => $data['purpose'] ?? null,
                'deposit_total' => $quote['fee'],
                'status' => Booking::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            SlotInventory::where('service_area_id', $booking->service_area_id)
                ->whereDate('booking_date', $booking->booking_date->toDateString())
                ->where('time_slot', $booking->time_slot)
                ->increment('reserved_units');

            return $booking;
        });
    }
}
