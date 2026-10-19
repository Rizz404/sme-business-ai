<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\BookingInput;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Booking\Support\SlotAvailability;

final class CheckAvailabilityTool implements Tool
{
    public function __construct(
        private readonly SlotAvailability $availability,
        private readonly BookingInput $input,
    ) {}

    public function name(): string
    {
        return 'check_availability';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Get the current, real-time availability and exact booking fee for one service area on a specific date and time slot. ALWAYS call this before telling a customer a slot is available or confirming a fee — never state availability from memory.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'service_area_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'attendees' => ['type' => 'integer', 'minimum' => 1],
                    ],
                    'required' => ['service_area_slug', 'date', 'time_slot'],
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

        $attendees = isset($arguments['attendees']) ? (int) $arguments['attendees'] : null;

        if ($attendees !== null && ! $this->availability->fitsAttendees($area, $attendees)) {
            return new ToolResult("This attendee count does not suit that service area (it seats {$area->min_attendees}-{$area->max_attendees} attendees). Suggest another service area, or call request_human_handover for a large group.");
        }

        $quote = $this->availability->isSlotInPast($business, $date, $slot)
            ? null
            : $this->availability->quote($area, $date, $slot);

        if (! $quote) {
            $alternatives = array_map(
                fn (string $s) => ['time_slot' => $s, 'time_range' => SlotAvailability::slotRange($s)],
                $this->availability->availableSlots($area, $date),
            );

            return ToolResult::json([
                'available' => false,
                'service_area_slug' => $area->slug,
                'message' => 'No free slot in this service area for that slot.',
                'other_available_slots_that_day' => $alternatives,
            ], ['type' => 'availability', 'available' => false, 'service_area_slug' => $area->slug]);
        }

        $result = [
            'available' => true,
            'service_area_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'open_units' => $quote['open_units'],
            'booking_fee' => $quote['fee'],
            'minimum_order' => $quote['minimum_order'],
            'currency' => $business->currency,
        ];

        return ToolResult::json($result, ['type' => 'availability', 'available' => true, 'quote' => $result]);
    }
}
