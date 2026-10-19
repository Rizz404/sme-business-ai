<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\HandoverRequest;
use App\Models\SlotInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Business $otherBusiness;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $this->otherBusiness = Business::create(['name' => 'Other', 'slug' => 'other', 'public_status' => 'published']);

        $this->staff = User::factory()->create();
        $this->business->users()->attach($this->staff->id, ['role' => 'owner', 'status' => 'active']);
    }

    private function foreignBooking(): Booking
    {
        $area = $this->otherBusiness->serviceAreas()->create(['name' => 'Foreign', 'slug' => 'foreign', 'area_type' => 'storefront', 'is_active' => true]);

        return Booking::create([
            'reference' => 'RS-FOREIGN', 'business_id' => $this->otherBusiness->id, 'service_area_id' => $area->id, 'customer_name' => 'Foreign Guest',
            'booking_date' => now()->addDays(3)->toDateString(), 'time_slot' => '11:00', 'attendees' => 2,
        ]);
    }

    private function foreignHandover(): HandoverRequest
    {
        $conversation = Conversation::create(['business_id' => $this->otherBusiness->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        return HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'complaint', 'summary' => 'Foreign complaint']);
    }

    public function test_visitors_are_sent_to_the_login_page_for_every_admin_screen(): void
    {
        foreach (['admin.dashboard', 'admin.catalog-items.index', 'admin.service-areas.index', 'admin.knowledge-items.index', 'admin.bookings.index', 'admin.handovers.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_staff_only_see_bookings_of_their_own_business(): void
    {
        $area = $this->business->serviceAreas()->create(['name' => 'Studio', 'slug' => 'studio', 'area_type' => 'storefront', 'is_active' => true]);
        Booking::create([
            'reference' => 'RS-MINE', 'business_id' => $this->business->id, 'service_area_id' => $area->id, 'customer_name' => 'My Guest',
            'booking_date' => now()->addDays(3)->toDateString(), 'time_slot' => '13:00', 'attendees' => 2,
        ]);
        $this->foreignBooking();

        $this->actingAs($this->staff)->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee('RS-MINE')
            ->assertDontSee('RS-FOREIGN');
    }

    public function test_staff_cannot_change_another_businesses_booking(): void
    {
        $booking = $this->foreignBooking();

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.status', $booking), ['status' => 'confirmed'])
            ->assertNotFound();

        $this->assertSame(Booking::STATUS_PENDING, $booking->fresh()->status);
    }

    public function test_cancelling_a_booking_releases_its_slot(): void
    {
        $area = $this->business->serviceAreas()->create(['name' => 'Studio', 'slug' => 'studio', 'area_type' => 'storefront', 'is_active' => true]);
        $date = now()->addDays(3)->toDateString();
        SlotInventory::create(['service_area_id' => $area->id, 'booking_date' => $date, 'time_slot' => '13:00', 'total_units' => 2, 'reserved_units' => 1]);
        $booking = Booking::create([
            'reference' => 'RS-MINE', 'business_id' => $this->business->id, 'service_area_id' => $area->id, 'customer_name' => 'My Guest',
            'booking_date' => $date, 'time_slot' => '13:00', 'attendees' => 2,
        ]);

        $this->actingAs($this->staff)
            ->patch(route('admin.bookings.status', $booking), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
        $this->assertSame(0, SlotInventory::firstOrFail()->reserved_units);
    }

    public function test_staff_cannot_open_or_answer_another_businesses_handover(): void
    {
        $handover = $this->foreignHandover();

        $this->actingAs($this->staff)->get(route('admin.handovers.show', $handover))->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Hi'])->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertNotFound();
    }

    public function test_resolving_a_handover_hands_the_conversation_back_to_the_assistant(): void
    {
        $conversation = Conversation::create(['business_id' => $this->business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en', 'status' => Conversation::STATUS_HANDED_OVER]);
        $handover = HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'private_event', 'summary' => 'Birthday for 20']);

        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Happy to help!'])->assertRedirect();
        $this->assertSame('Happy to help!', ConversationMessage::where('role', ConversationMessage::ROLE_STAFF)->firstOrFail()->content);

        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertRedirect(route('admin.handovers.index'));

        $this->assertSame(HandoverRequest::STATUS_RESOLVED, $handover->fresh()->status);
        $this->assertSame(Conversation::STATUS_ACTIVE, $conversation->fresh()->status);
    }

    public function test_staff_manage_the_catalog_of_their_own_business_only(): void
    {
        $this->actingAs($this->staff)->post(route('admin.catalog-items.store'), [
            'category' => 'product', 'name' => 'Poster Print', 'price' => 34000, 'tags' => 'Popular, New', 'requirements' => 'appointment', 'is_active' => '1',
        ])->assertRedirect(route('admin.catalog-items.index'));

        $item = $this->business->catalogItems()->firstOrFail();
        $this->assertSame('poster-print', $item->slug);
        $this->assertSame(['popular', 'new'], $item->tags);
        $this->assertTrue($item->is_active);

        $foreign = $this->otherBusiness->catalogItems()->create(['category' => 'product', 'name' => 'Foreign', 'slug' => 'foreign', 'price' => 1]);

        $this->actingAs($this->staff)->get(route('admin.catalog-items.edit', $foreign))->assertNotFound();
        $this->actingAs($this->staff)->delete(route('admin.catalog-items.destroy', $foreign))->assertNotFound();
        $this->assertNotNull($foreign->fresh());
    }

    public function test_service_area_capacity_is_validated(): void
    {
        $this->actingAs($this->staff)->post(route('admin.service-areas.store'), [
            'name' => 'Patio', 'area_type' => 'online', 'min_attendees' => 6, 'max_attendees' => 2, 'booking_fee' => 0,
        ])->assertSessionHasErrors('max_attendees');

        $this->actingAs($this->staff)->post(route('admin.service-areas.store'), [
            'name' => 'Patio', 'area_type' => 'online', 'min_attendees' => 1, 'max_attendees' => 6, 'booking_fee' => 0, 'features' => 'Video Call, wifi', 'is_active' => '1',
        ])->assertRedirect(route('admin.service-areas.index'));

        $this->assertSame(['video_call', 'wifi'], $this->business->serviceAreas()->firstOrFail()->features);
    }

    public function test_the_dashboard_counts_only_the_staffs_own_business(): void
    {
        $this->foreignHandover();
        $this->foreignBooking();

        $this->actingAs($this->staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['open_handovers'] === 0 && $stats['pending_bookings'] === 0);
    }

    public function test_an_account_without_a_business_is_turned_away(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }
}
