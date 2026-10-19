<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\ServiceArea;
use App\Models\SlotInventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessStageTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private CatalogItem $logo;

    private ServiceArea $workshop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->business = Business::create([
            'name' => 'Demo Business', 'slug' => 'demo', 'public_status' => 'published', 'city' => 'Jakarta', 'country' => 'Indonesia', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '08:00', 'closing_time' => '22:00', 'phone' => '+62 21 1234', 'whatsapp' => '6281234567890',
            'description' => 'Studio desain di Bandung.',
        ]);

        $this->logo = $this->business->catalogItems()->create([
            'name' => 'Logo Design', 'slug' => 'logo-design', 'category' => 'product', 'price' => 32000, 'description' => 'Desain logo untuk usaha.',
            'translations' => ['en' => ['name' => 'Logo', 'description' => 'A logo for your business.']], 'tags' => ['popular', 'signature'], 'requirements' => ['appointment'], 'delivery' => 'online',
            'image_url' => 'images/catalog/logo-design.svg', 'is_active' => true, 'sort_order' => 0,
        ]);
        $this->business->catalogItems()->create(['name' => 'Rush Add-on', 'slug' => 'rush-addon', 'category' => 'package', 'price' => 40000, 'is_unavailable' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->business->catalogItems()->create(['name' => 'Retired Offer', 'slug' => 'retired', 'category' => 'product', 'price' => 1000, 'is_active' => false]);

        $this->workshop = $this->business->serviceAreas()->create([
            'name' => 'Workshop Room', 'slug' => 'workshop-room', 'area_type' => 'online', 'min_attendees' => 1, 'max_attendees' => 6, 'features' => ['projector'],
            'booking_fee' => 0, 'is_active' => true,
        ]);
        $this->workshop->images()->create(['image_url' => 'images/service/workshop-room.svg', 'alt_text' => 'Terrace', 'sort_order' => 0]);
        SlotInventory::create([
            'service_area_id' => $this->workshop->id, 'booking_date' => CarbonImmutable::now('Asia/Jakarta')->addDays(3)->toDateString(), 'time_slot' => '13:00',
            'total_units' => 2, 'reserved_units' => 0,
        ]);

        $this->business->knowledgeItems()->create(['category' => 'facilities', 'title' => 'Wi-Fi gratis', 'body' => 'Wi-Fi cepat untuk semua tamu.', 'tags' => ['wifi'], 'is_active' => true]);
        $this->business->knowledgeItems()->create(['category' => 'policies', 'title' => 'Kebijakan pemesanan', 'body' => 'Batal gratis 2 jam sebelumnya.', 'is_active' => true]);
        $this->business->knowledgeItems()->create(['category' => 'faq', 'title' => 'Ada parkir?', 'body' => 'Ada, di depan studio.', 'is_active' => true]);
    }

    public function test_the_opening_screen_lists_published_businesses(): void
    {
        Business::create(['name' => 'Draft Business', 'slug' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Business')
            ->assertDontSee('Draft Business')
            ->assertSee(route('business.catalog', ['businessSlug' => 'demo', 'lang' => 'id']), false);
    }

    public function test_the_counter_greets_guests_in_the_requested_language(): void
    {
        $this->get('/demo')->assertOk()->assertSee('Demo Business')->assertSee('Selamat datang di');
        $this->get('/demo?lang=en')->assertOk()->assertSee('Welcome to')->assertSee('lang="en"', false);
        $this->get('/demo?lang=ja')->assertOk()->assertSee('ようこそ');
        $this->get('/demo?lang=xx')->assertOk()->assertSee('Selamat datang di');
    }

    public function test_every_scene_renders_and_carries_the_assistant_chat(): void
    {
        $routes = [
            'business.show', 'business.catalog', 'business.service', 'business.facilities', 'business.info', 'business.staff', 'business.booking',
        ];

        foreach ($routes as $route) {
            $this->get(route($route, ['businessSlug' => 'demo']))
                ->assertOk()
                ->assertSee('id="assistant-app"', false)
                ->assertSee('data-scene=', false);
        }
    }

    public function test_unpublished_and_unknown_businesses_are_not_found(): void
    {
        Business::create(['name' => 'Draft', 'slug' => 'draft']);

        $this->get('/draft')->assertNotFound();
        $this->get('/draft/catalog')->assertNotFound();
        $this->get('/nowhere')->assertNotFound();
    }

    public function test_the_catalog_scene_narrates_from_stored_data_and_lists_active_items(): void
    {
        $this->get('/demo/catalog?lang=en')
            ->assertOk()
            ->assertSee('We have 2 items, from IDR 32.000')
            ->assertSee('Logo')
            ->assertSee('Rush Add-on')
            ->assertDontSee('Retired Offer');
    }

    public function test_a_catalog_item_shows_its_price_requirements_and_tags(): void
    {
        $this->get('/demo/catalog/logo-design?lang=en')
            ->assertOk()
            ->assertSee('Logo')
            ->assertSee('IDR 32.000')
            ->assertSee('A logo for your business.')
            ->assertSee('Milk')
            ->assertSee('Signature')
            ->assertSee('Contains: milk.')
            ->assertSee('images/catalog/logo-design.svg', false);

        $this->get('/demo/catalog/rush-addon?lang=en')->assertOk()->assertSee('Sold out today');

        $this->get('/demo/catalog/retired')->assertNotFound();
        $this->get('/demo/catalog/nothing-here')->assertNotFound();
    }

    public function test_the_service_scenes_show_capacity_and_fees(): void
    {
        $this->get('/demo/service?lang=en')->assertOk()->assertSee('Workshop Room')->assertSee('Here is our service area, Garden Terrace.');

        $this->get('/demo/service/workshop-room?lang=en')
            ->assertOk()
            ->assertSee('Workshop Room')
            ->assertSee('Pet friendly')
            ->assertSee('There is no booking fee.')
            ->assertSee('area=workshop-room', false);

        $this->get('/demo/service/nowhere')->assertNotFound();
    }

    public function test_the_facility_scenes_show_stored_services(): void
    {
        $facility = $this->business->knowledgeItems()->where('category', 'facilities')->firstOrFail();

        $this->get('/demo/facilities')->assertOk()->assertSee('Wi-Fi gratis')->assertDontSee('Kebijakan pemesanan');
        $this->get('/demo/facilities/'.$facility->id)->assertOk()->assertSee('Wi-Fi cepat untuk semua tamu.');
        $this->get('/demo/facilities/99999')->assertNotFound();
    }

    public function test_the_info_scene_shows_opening_hours_policies_and_faq(): void
    {
        $this->get('/demo/info?lang=en')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('22:00')
            ->assertSee('We are open from 08:00 to 22:00.')
            ->assertSee('Kebijakan pemesanan')
            ->assertSee('Ada parkir?')
            ->assertSee('Studio desain di Bandung.');
    }

    public function test_the_staff_scene_offers_whatsapp_and_phone(): void
    {
        $this->get('/demo/staff')
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+62211234', false);
    }

    public function test_the_booking_scene_offers_slots_and_service_areas(): void
    {
        $this->get('/demo/booking?area=workshop-room&lang=en')
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('data-preselect-area="workshop-room"', false)
            ->assertSee('13:00–15:00')
            ->assertSee('Workshop Room');
    }
}
