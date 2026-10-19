<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\ServiceArea;
use App\Models\SlotInventory;
use App\Modules\Booking\Actions\ChangeBookingStatus;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private ServiceArea $studio;

    private ServiceArea $workshop;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR', 'default_locale' => 'en', 'whatsapp' => '6281234567890', 'email' => 'hello@demo.test']);
        $this->studio = $this->business->serviceAreas()->create(['name' => 'Studio Front', 'slug' => 'studio-front', 'area_type' => 'storefront', 'min_attendees' => 1, 'max_attendees' => 4, 'booking_fee' => 0, 'is_active' => true]);
        $this->workshop = $this->business->serviceAreas()->create(['name' => 'Workshop Room', 'slug' => 'workshop-room', 'area_type' => 'online', 'min_attendees' => 1, 'max_attendees' => 6, 'booking_fee' => 25000, 'is_active' => true]);

        $this->date = CarbonImmutable::now($this->business->timezone)->addDays(5)->toDateString();

        foreach ([$this->studio, $this->workshop] as $area) {
            foreach (['11:00', '13:00'] as $slot) {
                SlotInventory::create(['service_area_id' => $area->id, 'booking_date' => $this->date, 'time_slot' => $slot, 'total_units' => 1, 'reserved_units' => 0]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'service_area_slug' => 'studio-front', 'booking_date' => $this->date, 'time_slot' => '11:00', 'attendees' => 2,
            'customer_name' => 'Ayu', 'contact_type' => 'whatsapp', 'contact_value' => '+62 811 1234 5678', 'locale' => 'en',
            ...$overrides,
        ];
    }

    public function test_a_quote_reports_the_fee_and_open_units(): void
    {
        $this->postJson('/demo/booking/quote', $this->payload(['service_area_slug' => 'workshop-room']))
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('fee', 25000)
            ->assertJsonPath('time_range', '11:00–13:00')
            ->assertJsonPath('open_units', 1);
    }

    public function test_submitting_creates_a_pending_booking_and_holds_a_slot(): void
    {
        $this->postJson('/demo/booking', $this->payload(['purpose' => 'consultation', 'special_request' => 'Bring a demo unit please']))
            ->assertCreated()
            ->assertJsonPath('status', Booking::STATUS_PENDING)
            ->assertJsonStructure(['reference', 'handover' => ['whatsapp_url', 'phone_url', 'email_url']]);

        $booking = Booking::firstOrFail();
        $this->assertStringStartsWith('RS-', $booking->reference);
        $this->assertSame('consultation', $booking->purpose);
        $this->assertSame('Bring a demo unit please', $booking->notes);
        $this->assertSame(1, SlotInventory::where('service_area_id', $this->studio->id)->where('time_slot', '11:00')->value('reserved_units'));
    }

    public function test_a_full_slot_is_refused_with_alternatives(): void
    {
        SlotInventory::where('service_area_id', $this->studio->id)->where('time_slot', '11:00')->update(['reserved_units' => 1]);

        $response = $this->postJson('/demo/booking', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_area_slug');

        $alternatives = collect($response->json('alternatives'));
        $this->assertSame('13:00', $alternatives->firstWhere('type', 'slot')['time_slot']);
        $this->assertSame('workshop-room', $alternatives->firstWhere('type', 'area')['slug']);
        $this->assertSame(0, Booking::count());
    }

    public function test_the_party_must_suit_the_service_area(): void
    {
        $this->postJson('/demo/booking', $this->payload(['attendees' => 5]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attendees');
    }

    public function test_dates_and_slots_are_validated(): void
    {
        $this->postJson('/demo/booking', $this->payload(['booking_date' => '2001-01-01']))->assertUnprocessable()->assertJsonValidationErrors('booking_date');

        $far = CarbonImmutable::now($this->business->timezone)->addDays(SlotAvailability::MAX_ADVANCE_DAYS + 5)->toDateString();
        $this->postJson('/demo/booking', $this->payload(['booking_date' => $far]))->assertUnprocessable()->assertJsonValidationErrors('booking_date');

        $this->postJson('/demo/booking', $this->payload(['time_slot' => '03:00']))->assertUnprocessable()->assertJsonValidationErrors('time_slot');
        $this->postJson('/demo/booking', $this->payload(['service_area_slug' => 'nowhere']))->assertUnprocessable()->assertJsonValidationErrors('service_area_slug');
    }

    public function test_contact_details_are_checked_for_the_chosen_method(): void
    {
        $this->postJson('/demo/booking', $this->payload(['contact_type' => 'email', 'contact_value' => 'not-an-email']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/booking', $this->payload(['contact_type' => 'phone', 'contact_value' => 'abc']))->assertUnprocessable()->assertJsonValidationErrors('contact_value');
        $this->postJson('/demo/booking', $this->payload(['contact_type' => 'email', 'contact_value' => 'ayu@example.com']))->assertCreated();

        $this->assertSame('ayu@example.com', Booking::firstOrFail()->customer_email);
    }

    public function test_a_booking_links_to_the_conversation_it_came_from(): void
    {
        $conversation = Conversation::create(['business_id' => $this->business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $this->postJson('/demo/booking', $this->payload(['guest_token' => $conversation->guest_token]))->assertCreated();

        $this->assertSame($conversation->id, Booking::firstOrFail()->conversation_id);
    }

    public function test_cancelling_gives_the_slot_back(): void
    {
        $this->postJson('/demo/booking', $this->payload())->assertCreated();
        $booking = Booking::firstOrFail();

        app(ChangeBookingStatus::class)->releaseUnit($booking);

        $this->assertSame(0, SlotInventory::where('service_area_id', $this->studio->id)->where('time_slot', '11:00')->value('reserved_units'));
    }

    public function test_unpublished_businesses_take_no_bookings(): void
    {
        $this->business->update(['public_status' => 'draft']);

        $this->postJson('/demo/booking/quote', $this->payload())->assertNotFound();
        $this->postJson('/demo/booking', $this->payload())->assertNotFound();
    }
}
