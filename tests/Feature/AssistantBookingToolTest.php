<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\SlotInventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\RunsAssistantTools;
use Tests\TestCase;

class AssistantBookingToolTest extends TestCase
{
    use RefreshDatabase, RunsAssistantTools;

    public function test_ai_booking_tool_creates_a_reference_and_holds_one_slot(): void
    {
        $business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $area = $business->serviceAreas()->create(['name' => 'Private Room', 'slug' => 'private-room', 'area_type' => 'private', 'min_attendees' => 6, 'max_attendees' => 12, 'booking_fee' => 150000, 'is_active' => true]);
        $conversation = Conversation::create(['business_id' => $business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $date = CarbonImmutable::now($business->timezone)->addDays(5);
        foreach (['11:00', '13:00'] as $slot) {
            SlotInventory::create(['service_area_id' => $area->id, 'booking_date' => $date->toDateString(), 'time_slot' => $slot, 'total_units' => 1, 'reserved_units' => 0]);
        }

        $input = [
            'service_area_slug' => 'private-room', 'date' => $date->toDateString(), 'time_slot' => '11:00',
            'attendees' => 8, 'customer_name' => 'Ayu', 'customer_phone' => '+62 811 111 222',
        ];

        $quote = $this->runTool($business, $conversation, 'check_availability', $input);
        $this->assertTrue($quote['ui']['available']);
        $this->assertSame(150000.0, $quote['ui']['quote']['booking_fee']);

        $result = $this->runTool($business, $conversation, 'create_booking_request', $input);

        $booking = Booking::firstOrFail();
        $this->assertSame($booking->reference, $result['ui']['booking']['booking_reference']);
        $this->assertSame(8, $booking->attendees);
        $this->assertSame('11:00', $booking->time_slot);
        $this->assertSame(150000.0, (float) $booking->deposit_total);
        $this->assertSame([1, 0], SlotInventory::orderBy('time_slot')->pluck('reserved_units')->all());

        $repeated = $this->runTool($business, $conversation, 'create_booking_request', $input);
        $this->assertSame($booking->reference, $repeated['ui']['booking']['booking_reference']);
        $this->assertSame(1, Booking::count());

        $taken = $this->runTool($business, $conversation, 'create_booking_request', [...$input, 'customer_name' => 'Budi']);
        $this->assertNull($taken['ui']);
        $this->assertSame(1, Booking::count());

        $full = $this->runTool($business, $conversation, 'check_availability', $input);
        $this->assertFalse($full['ui']['available']);
        $this->assertStringContainsString('13:00', $full['text']);
    }

    public function test_the_tools_refuse_a_party_that_does_not_fit_and_a_slot_that_does_not_exist(): void
    {
        $business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $business->serviceAreas()->create(['name' => 'Bar', 'slug' => 'bar', 'area_type' => 'bar', 'min_attendees' => 1, 'max_attendees' => 2, 'is_active' => true]);
        $conversation = Conversation::create(['business_id' => $business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($business->timezone)->addDays(2);
        SlotInventory::create(['service_area_id' => $area->id, 'booking_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_units' => 2, 'reserved_units' => 0]);

        $base = ['service_area_slug' => 'bar', 'date' => $date->toDateString(), 'customer_name' => 'Ayu', 'customer_phone' => '+62 811 111 222'];

        $tooMany = $this->runTool($business, $conversation, 'create_booking_request', [...$base, 'time_slot' => '15:00', 'attendees' => 5]);
        $this->assertNull($tooMany['ui']);

        $unknownSlot = $this->runTool($business, $conversation, 'check_availability', [...$base, 'time_slot' => '03:00', 'attendees' => 2]);
        $this->assertStringContainsString('15:00', $unknownSlot['text']);

        $past = $this->runTool($business, $conversation, 'check_availability', [...$base, 'date' => '2001-01-01', 'time_slot' => '15:00', 'attendees' => 2]);
        $this->assertStringContainsString('past', $past['text']);

        $this->assertSame(0, Booking::count());
    }

    public function test_the_booking_tool_needs_a_usable_name_and_contact_before_it_books(): void
    {
        $business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $business->serviceAreas()->create(['name' => 'Bar', 'slug' => 'bar', 'area_type' => 'bar', 'min_attendees' => 1, 'max_attendees' => 4, 'is_active' => true]);
        $conversation = Conversation::create(['business_id' => $business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($business->timezone)->addDays(2);
        SlotInventory::create(['service_area_id' => $area->id, 'booking_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_units' => 2, 'reserved_units' => 0]);

        $base = ['service_area_slug' => 'bar', 'date' => $date->toDateString(), 'time_slot' => '15:00', 'attendees' => 2];

        foreach ([
            ['customer_phone' => '+62 811 111 222'],
            ['customer_name' => 'Ayu'],
            ['customer_name' => 'Ayu', 'customer_phone' => 'call me'],
            ['customer_name' => 'Ayu', 'customer_email' => 'not-an-email'],
        ] as $guest) {
            $this->assertNull($this->runTool($business, $conversation, 'create_booking_request', [...$base, ...$guest])['ui']);
        }

        $this->assertSame(0, Booking::count());

        $byEmail = $this->runTool($business, $conversation, 'create_booking_request', [...$base, 'customer_name' => 'Ayu', 'customer_email' => 'ayu@example.com']);

        $this->assertNotNull($byEmail['ui']);
        $this->assertSame('email', Booking::firstOrFail()->contact_type);
    }

    public function test_catalog_search_filters_by_tag_and_budget_and_hides_unpublished_items(): void
    {
        $business = Business::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $business->catalogItems()->create(['name' => 'Starter Print', 'slug' => 'starter-print', 'category' => 'service', 'price' => 48000, 'tags' => ['popular'], 'is_active' => true]);
        $business->catalogItems()->create(['name' => 'Banner Print', 'slug' => 'banner-print', 'category' => 'service', 'price' => 52000, 'tags' => ['promo'], 'is_active' => true]);
        $business->catalogItems()->create(['name' => 'Secret Item', 'slug' => 'secret', 'category' => 'service', 'price' => 10000, 'tags' => ['popular'], 'is_active' => false]);
        $conversation = Conversation::create(['business_id' => $business->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $popular = $this->runTool($business, $conversation, 'search_catalog', ['tag' => 'popular']);
        $this->assertSame(['starter-print'], collect($popular['ui']['items'])->pluck('catalog_item_slug')->all());

        $cheap = $this->runTool($business, $conversation, 'search_catalog', ['category' => 'service', 'max_price' => 50000]);
        $this->assertSame(['starter-print'], collect($cheap['ui']['items'])->pluck('catalog_item_slug')->all());

        $none = $this->runTool($business, $conversation, 'search_catalog', ['query' => 'sushi']);
        $this->assertNull($none['ui']);
    }
}
