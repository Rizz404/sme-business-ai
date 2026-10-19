<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResolveBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_business_is_not_found_on_pages_and_on_json_endpoints(): void
    {
        $this->get('/nowhere')->assertNotFound();
        $this->postJson('/nowhere/assistant/start')->assertNotFound();
        $this->postJson('/nowhere/booking/quote')->assertNotFound();
    }

    public function test_a_draft_business_is_not_public(): void
    {
        $business = Business::factory()->draft()->create(['slug' => 'soon']);

        $this->get('/'.$business->slug)->assertNotFound();
        $this->postJson('/'.$business->slug.'/assistant/start')->assertNotFound();
    }

    public function test_the_page_language_comes_from_the_query_or_falls_back_to_the_business_default(): void
    {
        Business::factory()->create(['slug' => 'demo', 'default_locale' => 'id']);

        $this->get('/demo?lang=en')->assertOk()->assertSee('lang="en"', false);
        $this->get('/demo?lang=xx')->assertOk()->assertSee('lang="id"', false);
    }

    public function test_a_chat_starts_in_the_requested_supported_language(): void
    {
        Business::factory()->create(['slug' => 'demo', 'default_locale' => 'id']);

        $this->postJson('/demo/assistant/start', ['locale' => 'ja'])->assertOk()->assertJsonPath('locale', 'ja');
        $this->postJson('/demo/assistant/start', ['locale' => 'xx'])->assertOk()->assertJsonPath('locale', 'id');
    }

    public function test_every_response_carries_a_request_id_and_a_valid_incoming_one_is_reused(): void
    {
        Business::factory()->create(['slug' => 'demo']);

        $generated = $this->get('/demo')->headers->get('X-Request-Id');
        $this->assertTrue(Str::isUuid($generated));

        $incoming = (string) Str::uuid();
        $this->get('/demo', ['X-Request-Id' => $incoming])->assertHeader('X-Request-Id', $incoming);

        $this->get('/demo', ['X-Request-Id' => 'not-a-uuid'])->assertHeader('X-Request-Id');
        $this->assertNotSame('not-a-uuid', $this->get('/demo', ['X-Request-Id' => 'not-a-uuid'])->headers->get('X-Request-Id'));
    }
}
