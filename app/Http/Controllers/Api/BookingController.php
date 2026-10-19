<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveBusiness;
use App\Http\Requests\Api\QuoteBookingRequest;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\ServiceArea;
use App\Modules\Booking\Actions\CreateBookingRequest;
use App\Modules\Booking\Actions\ResolveBookingTarget;
use App\Modules\Booking\Queries\FindAlternatives;
use App\Modules\Booking\Support\BookingHandover;
use App\Modules\Booking\Support\SlotAvailability;
use App\Modules\Booking\Validation\BookingRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly SlotAvailability $availability,
        private readonly ResolveBookingTarget $resolveTarget,
        private readonly FindAlternatives $findAlternatives,
    ) {}

    public function quote(QuoteBookingRequest $request): JsonResponse
    {
        $business = ResolveBusiness::business($request);
        $locale = ResolveBusiness::locale($request);

        $data = $request->validated();
        [$area, $date] = $this->resolveTarget->handle($business, $data, $locale);

        $quote = $this->availability->quote($area, $date, $data['time_slot']);

        if (! $quote) {
            return $this->unavailable($business, $area, $date, $data, $locale);
        }

        return response()->json([
            'available' => true,
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'open_units' => $quote['open_units'],
            'fee' => $quote['fee'],
            'minimum_order' => $quote['minimum_order'],
            'currency' => $business->currency,
        ]);
    }

    public function store(
        StoreBookingRequest $request,
        CreateBookingRequest $create,
        BookingHandover $handover,
    ): JsonResponse {
        $business = ResolveBusiness::business($request);
        $locale = ResolveBusiness::locale($request);

        $data = $request->validated();
        $isEmail = $request->isEmailContact();

        [$area, $date] = $this->resolveTarget->handle($business, $data, $locale);

        $conversation = isset($data['guest_token'])
            ? Conversation::where('business_id', $business->id)->where('guest_token', $data['guest_token'])->first()
            : null;

        $booking = $create->handle($business, $area, [
            'booking_date' => $date,
            'time_slot' => $data['time_slot'],
            'attendees' => (int) $data['attendees'],
            'purpose' => $data['purpose'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_email' => $isEmail ? $data['contact_value'] : null,
            'customer_phone' => $isEmail ? null : $data['contact_value'],
            'contact_type' => $data['contact_type'],
            'notes' => $data['special_request'] ?? null,
        ], $conversation);

        if (! $booking) {
            return $this->unavailable($business, $area, $date, $data, $locale);
        }

        return response()->json([
            'reference' => $booking->reference,
            'status' => $booking->status,
            'fee' => (float) $booking->deposit_total,
            'currency' => $business->currency,
            'handover' => $handover->forBooking($business, $booking, $locale),
        ], 201);
    }

    /**
     * No slot is free in that area and slot: say so, and offer what is.
     *
     * @param  array<string, mixed>  $data
     */
    private function unavailable(Business $business, ServiceArea $requested, CarbonImmutable $date, array $data, string $locale): JsonResponse
    {
        $message = BookingRules::message('unavailable', $locale);

        return response()->json([
            'message' => $message,
            'errors' => ['service_area_slug' => [$message]],
            'alternatives' => $this->findAlternatives->handle($business, $requested, $date, $data['time_slot'], (int) $data['attendees'], $locale),
            'currency' => $business->currency,
        ], 422);
    }
}
