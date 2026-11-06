<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business experience
    |--------------------------------------------------------------------------
    |
    | Guest-facing settings shared by every business. Per-business values (name,
    | currency, opening hours, default locale) live in the businesses table.
    | Provider connection values stay in config/services.php under
    | "local_llm"; AI budgets are in config/assistant.php.
    |
    */

    'locales' => ['id', 'en', 'ja'],

    'default_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Default booking slots
    |--------------------------------------------------------------------------
    |
    | Start times (two hours each) and parallel units used by
    | `sme:open-inventory` for a service area that has no slots yet. Areas that
    | already have slots keep their own pattern.
    |
    */

    'default_slots' => ['09:00', '11:00', '13:00', '15:00'],

    'default_units' => 1,

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | Comma-separated IPs/CIDRs of the reverse proxies in front of the app
    | (for example Cloudflare), so the real visitor address is used for rate
    | limits. Leave empty when the app is not behind a proxy; never use "*".
    |
    */

    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Stage artwork
    |--------------------------------------------------------------------------
    |
    | The small round picture of the assistant used beside chat and narration,
    | and the artwork per scene: a plain illustrated background plus one
    | cut-out of the assistant standing in front of it. The background fills
    | the tall left pane (object-position "focus") or the strip above the
    | folder on a phone ("focusMobile"). Scenes without artwork of their own
    | borrow another scene's. Regenerate every file with
    | `python scripts/generate-stage-assets.py`.
    |
    */

    'avatar_image' => 'images/assistant/avatar.svg',

    'scenes' => [
        'home' => ['image' => 'images/scenes/storefront.svg', 'character' => 'images/character/assistant-greeting.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'catalog' => ['image' => 'images/scenes/catalog.svg', 'character' => 'images/character/assistant-notepad.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'service' => ['image' => 'images/scenes/workspace.svg', 'character' => 'images/character/assistant-clasped.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'facilities' => ['image' => 'images/scenes/studio.svg', 'character' => 'images/character/assistant-greeting.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'info' => ['image' => 'images/scenes/office.svg', 'character' => 'images/character/assistant-clasped.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'staff' => ['image' => 'images/scenes/team.svg', 'character' => 'images/character/assistant-clasped.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
        'booking' => ['image' => 'images/scenes/booking.svg', 'character' => 'images/character/assistant-notepad.svg', 'focus' => '50% 50%', 'focusMobile' => '50% 50%'],
    ],

    'scene_artwork' => [
        'catalog-item' => 'catalog',
        'service-area' => 'service',
        'facility' => 'facilities',
    ],

];
