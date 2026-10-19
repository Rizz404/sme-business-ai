<?php

namespace App\Modules\Booking\Support;

use App\Models\Booking;
use App\Models\Business;

/**
 * Builds the WhatsApp / phone / email hand-over links that carry a
 * booking summary (or a plain "talk to staff" request) to the business team.
 */
class BookingHandover
{
    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forBooking(Business $business, Booking $booking, string $locale): array
    {
        $message = $this->bookingMessage($booking, $locale);

        return $this->links($business, $message, $this->subject('booking', $locale).' '.$booking->reference);
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forStaff(Business $business, string $locale): array
    {
        return $this->links($business, $this->subject('staff_message', $locale), $this->subject('staff', $locale));
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    private function links(Business $business, string $message, string $subject): array
    {
        $whatsapp = preg_replace('/\D+/', '', (string) $business->whatsapp);
        $phone = preg_replace('/[^\d+]/', '', (string) $business->phone);

        return [
            'whatsapp_url' => $whatsapp !== '' ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode($message) : null,
            'phone_url' => $phone !== '' ? 'tel:'.$phone : null,
            'email_url' => filled($business->email) ? 'mailto:'.$business->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($message) : null,
        ];
    }

    private function bookingMessage(Booking $booking, string $locale): string
    {
        $booking->loadMissing('serviceArea');

        $label = fn (string $key): string => trans('handover.message.'.$key, [], $locale);

        return implode("\n", [
            $label('intro'),
            '',
            $label('name').": {$booking->customer_name}",
            $label('date').': '.$booking->booking_date->locale($locale)->translatedFormat('l, j F Y'),
            $label('time').': '.SlotAvailability::slotRange($booking->time_slot),
            $label('attendees').": {$booking->attendees}",
            $label('service').': '.$booking->serviceArea->translatedName($locale),
            $label('purpose').': '.($booking->purpose ?: '-'),
            $label('special').': '.($booking->notes ?: '-'),
            '',
            $label('reference').": {$booking->reference}",
        ]);
    }

    private function subject(string $key, string $locale): string
    {
        return trans('handover.subject.'.$key, [], $locale);
    }
}
