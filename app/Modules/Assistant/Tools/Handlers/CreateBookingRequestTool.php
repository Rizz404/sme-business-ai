<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Booking;
use App\Modules\Assistant\Tools\Support\BookingInput;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Booking\Actions\CreateBookingRequest;
use App\Modules\Booking\Support\BookingHandover;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;

/**
 * Only ever creates a pending request for the business to confirm; it never
 * confirms a slot and takes no payment.
 */
final class CreateBookingRequestTool implements Tool
{
    public function __construct(
        private readonly SlotAvailability $availability,
        private readonly BookingInput $input,
        private readonly CreateBookingRequest $create,
        private readonly BookingHandover $handover,
    ) {}

    public function name(): string
    {
        return 'create_booking_request';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Create a booking request once the customer has said yes to the full summary (service area, date, time slot, attendee count, name, and a phone/WhatsApp number or email). Call it once per booking. This re-checks availability before booking. It does not charge payment — it creates a pending booking for the business to confirm, and the customer sees the reference card on screen.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'service_area_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'attendees' => ['type' => 'integer', 'minimum' => 1],
                        'purpose' => ['type' => 'string', 'enum' => Booking::PURPOSES],
                        'customer_name' => ['type' => 'string'],
                        'customer_phone' => ['type' => 'string', 'description' => 'Phone or WhatsApp number. Required unless customer_email is given.'],
                        'customer_email' => ['type' => 'string', 'description' => 'Required unless customer_phone is given.'],
                        'notes' => ['type' => 'string', 'description' => 'Special request, at most 500 characters'],
                    ],
                    'required' => ['service_area_slug', 'date', 'time_slot', 'attendees', 'customer_name'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $business = $context->business;
        $area = CatalogSummaries::serviceAreaBySlug($business, (string) ($arguments['service_area_slug'] ?? ''));

        if (! $area) {
            return new ToolResult('Service area not found.');
        }

        $date = $this->input->parseDate($business, (string) ($arguments['date'] ?? ''));
        $slot = (string) ($arguments['time_slot'] ?? '');

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($business, $date)) {
            return new ToolResult($error);
        }

        if (! $this->input->isKnownSlot($business, $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($business));
        }

        if ($problem = $this->input->guestProblem($arguments)) {
            return new ToolResult($problem);
        }

        $attendees = (int) ($arguments['attendees'] ?? 0);

        if (! $this->availability->fitsAttendees($area, $attendees)) {
            return new ToolResult("This attendee count does not suit that service area (it seats {$area->min_attendees}-{$area->max_attendees} attendees). Suggest another service area, or call request_human_handover for a large group.");
        }

        if ($this->availability->isSlotInPast($business, $date, $slot)) {
            return new ToolResult('That time slot has already started. Ask the customer for a later slot.');
        }

        $customerName = trim((string) $arguments['customer_name']);

        if ($existing = $this->alreadyRequested($context, $area->id, $date, $slot, $attendees, $customerName)) {
            return $this->confirmation($context, $existing, alreadyRequested: true);
        }

        $purpose = in_array($arguments['purpose'] ?? null, Booking::PURPOSES, true) ? $arguments['purpose'] : null;
        $phone = trim((string) ($arguments['customer_phone'] ?? '')) ?: null;
        $email = trim((string) ($arguments['customer_email'] ?? '')) ?: null;

        $booking = $this->create->handle($business, $area, [
            'booking_date' => $date,
            'time_slot' => $slot,
            'attendees' => $attendees,
            'purpose' => $purpose,
            'customer_name' => $customerName,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'contact_type' => $phone ? 'phone' : 'email',
            'notes' => trim((string) ($arguments['notes'] ?? '')) ?: null,
        ], $context->conversation);

        if (! $booking) {
            return new ToolResult('That slot is no longer free — availability may have just changed. Call check_availability again or offer another slot.');
        }

        return $this->confirmation($context, $booking);
    }

    /**
     * The model may repeat the call when the customer says yes twice; the same
     * request in the same conversation is answered, not booked again.
     */
    private function alreadyRequested(ToolContext $context, int $areaId, CarbonImmutable $date, string $slot, int $attendees, string $customerName): ?Booking
    {
        return Booking::query()
            ->where('conversation_id', $context->conversation->id)
            ->where('service_area_id', $areaId)
            ->whereDate('booking_date', $date->toDateString())
            ->where('time_slot', $slot)
            ->where('attendees', $attendees)
            ->where('customer_name', $customerName)
            ->where('status', Booking::STATUS_PENDING)
            ->first();
    }

    private function confirmation(ToolContext $context, Booking $booking, bool $alreadyRequested = false): ToolResult
    {
        $area = $booking->serviceArea;

        $payload = [
            'booking_reference' => $booking->reference,
            'service_area_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $booking->booking_date->toDateString(),
            'time_slot' => $booking->time_slot,
            'time_range' => SlotAvailability::slotRange($booking->time_slot),
            'attendees' => $booking->attendees,
            'deposit_total' => (float) $booking->deposit_total,
            'currency' => $context->business->currency,
            'status' => 'pending_confirmation',
        ];

        return ToolResult::json(
            $alreadyRequested ? [...$payload, 'note' => 'This request was already created earlier in this conversation; do not book again.'] : $payload,
            [
                'type' => 'booking_confirmation',
                'booking' => $payload,
                'handover' => $this->handover->forBooking($context->business, $booking, $context->locale),
            ],
        );
    }
}
