<?php

namespace App\Modules\Booking\Validation;

use App\Models\Booking;
use App\Models\Business;
use Illuminate\Validation\Rule;

/**
 * The one set of booking rules, shared by the guest-facing wizard's form
 * requests so the quote step and the final submission can never disagree.
 */
final class BookingRules
{
    public const PHONE_PATTERN = '/^\+?[0-9\s\-().]{6,20}$/';

    public const CONTACT_TYPES = ['whatsapp', 'phone', 'email'];

    /**
     * What the guest asks for: where, when and how many.
     *
     * @return array<string, mixed>
     */
    public static function slot(Business $business): array
    {
        $today = now($business->timezone)->toDateString();

        return [
            'service_area_slug' => ['required', 'string', 'max:120'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'time_slot' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'attendees' => ['required', 'integer', 'min:1', 'max:30'],
            'purpose' => ['nullable', Rule::in(Booking::PURPOSES)],
            'locale' => ['nullable', Rule::in(config('business.locales'))],
        ];
    }

    /**
     * Who is asking, and how to reach them.
     *
     * @return array<string, mixed>
     */
    public static function guest(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'contact_type' => ['required', Rule::in(self::CONTACT_TYPES)],
            'contact_value' => ['required', 'string', 'max:120'],
            'special_request' => ['nullable', 'string', 'max:500'],
            'guest_token' => ['nullable', 'uuid'],
        ];
    }

    /**
     * Guest-facing wording: every generic rule failure reads the same.
     *
     * @return array<string, string>
     */
    public static function messages(string $locale): array
    {
        $messages = trans('booking.messages', [], $locale);

        return [
            'required' => $messages['invalid'],
            'date_format' => $messages['invalid'],
            'integer' => $messages['invalid'],
            'min' => $messages['invalid'],
            'max' => $messages['invalid'],
            'regex' => $messages['invalid'],
            'in' => $messages['invalid'],
            'uuid' => $messages['invalid'],
            'after_or_equal' => $messages['date_past'],
        ];
    }

    /**
     * The guest-facing message for a domain failure, e.g. "capacity".
     *
     * @param  array<string, string>  $replace
     */
    public static function message(string $key, string $locale, array $replace = []): string
    {
        return trans('booking.messages.'.$key, $replace, $locale);
    }
}
