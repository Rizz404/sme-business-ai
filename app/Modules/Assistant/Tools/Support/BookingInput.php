<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\Business;
use App\Modules\Booking\Support\SlotAvailability;
use App\Modules\Booking\Validation\BookingRules;
use Carbon\CarbonImmutable;

/**
 * Checks the date and time slot the model passes to the booking tools, and
 * words the problem so the model can ask the customer for something that works.
 */
class BookingInput
{
    public function __construct(private readonly SlotAvailability $availability) {}

    public function parseDate(Business $business, string $value): ?CarbonImmutable
    {
        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value, $business->timezone)?->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A reason the date cannot be booked, or null when it is fine.
     */
    public function dateProblem(Business $business, CarbonImmutable $date): ?string
    {
        $today = CarbonImmutable::now($business->timezone)->startOfDay();

        if ($date->lessThan($today)) {
            return 'That date is in the past. Ask the customer for a date from today onwards.';
        }

        if ($today->diffInDays($date) > SlotAvailability::MAX_ADVANCE_DAYS) {
            return 'Bookings can only be requested up to '.SlotAvailability::MAX_ADVANCE_DAYS.' days ahead. Offer an earlier date, or call request_human_handover for something further out.';
        }

        return null;
    }

    /**
     * A reason the customer's details cannot be used, or null when they are fine.
     * The model decides what to pass, so the same limits as the wizard apply.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function guestProblem(array $arguments): ?string
    {
        $name = trim((string) ($arguments['customer_name'] ?? ''));
        $phone = trim((string) ($arguments['customer_phone'] ?? ''));
        $email = trim((string) ($arguments['customer_email'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100) {
            return 'customer_name is missing or too long (100 characters at most). Ask the customer for their name.';
        }

        if ($phone === '' && $email === '') {
            return 'No way to reach the customer. Ask for a phone or WhatsApp number, or an email address, so the business can confirm.';
        }

        if ($phone !== '' && ! preg_match(BookingRules::PHONE_PATTERN, $phone)) {
            return 'That phone number does not look valid. Ask the customer to check it.';
        }

        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'That email address does not look valid. Ask the customer to check it.';
        }

        if (mb_strlen((string) ($arguments['notes'] ?? '')) > 500) {
            return 'notes is too long (500 characters at most). Shorten it, or call request_human_handover for a detailed request.';
        }

        return null;
    }

    public function isKnownSlot(Business $business, string $slot): bool
    {
        return in_array($slot, $this->availability->slotsFor($business), true);
    }

    public function unknownSlotMessage(Business $business): string
    {
        return 'time_slot must be one of the booking slots: '.implode(', ', $this->availability->slotsFor($business)).'.';
    }
}
