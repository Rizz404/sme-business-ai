<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\ServiceArea;
use App\Modules\Assistant\Tools\Support\BookingInput;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Booking\Support\SlotAvailability;

final class SearchServiceAreaTool implements Tool
{
    public function __construct(
        private readonly SlotAvailability $availability,
        private readonly BookingInput $input,
    ) {}

    public function name(): string
    {
        return 'search_service_area';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Find service areas that fit the customer\'s date and attendee count, with real-time slot availability already checked. Optionally narrow to one time slot. Use this whenever a customer describes what they want (date, time, attendee count, on-site or online) rather than naming one area.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Booking date, YYYY-MM-DD'],
                        'attendees' => ['type' => 'integer', 'minimum' => 1],
                        'time_slot' => ['type' => 'string', 'description' => 'Optional slot start time, HH:MM, one of the booking slots'],
                        'area_type' => ['type' => 'string', 'enum' => ServiceArea::areaTypes(), 'description' => 'Optional preference'],
                    ],
                    'required' => ['date', 'attendees'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $business = $context->business;
        $date = $this->input->parseDate($business, (string) ($arguments['date'] ?? ''));

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($business, $date)) {
            return new ToolResult($error);
        }

        $attendees = (int) ($arguments['attendees'] ?? 0);
        $slot = $arguments['time_slot'] ?? null;
        $areaType = $arguments['area_type'] ?? null;

        if ($slot && ! $this->input->isKnownSlot($business, (string) $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($business));
        }

        $matches = [];

        foreach ($business->serviceAreas()->where('is_active', true)->with('images')->orderBy('sort_order')->get() as $area) {
            if (! $this->availability->fitsAttendees($area, $attendees) || ($areaType && $area->area_type !== $areaType)) {
                continue;
            }

            $slots = $this->availability->availableSlots($area, $date);

            if ($slot) {
                $slots = array_values(array_intersect($slots, [$slot]));
            }

            if ($slots === []) {
                continue;
            }

            $matches[] = [
                'service_area_slug' => $area->slug,
                ...CatalogSummaries::serviceArea($area, $business, $context->locale),
                'date' => $date->toDateString(),
                'available_slots' => array_map(fn (string $s) => ['time_slot' => $s, 'time_range' => SlotAvailability::slotRange($s)], $slots),
            ];
        }

        if ($matches === []) {
            return new ToolResult('No service area has a free slot for that date, attendee count and time. Tell the customer honestly and offer a different time or date, or call request_human_handover for a large group.');
        }

        return ToolResult::json($matches, [
            'type' => 'service_results',
            'date' => $date->toDateString(),
            'attendees' => $attendees,
            'areas' => $matches,
        ]);
    }
}
