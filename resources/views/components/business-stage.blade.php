@props(['business', 'locale', 'supportedLocales', 'labels', 'stageCopy', 'narration', 'scene', 'backdrop', 'navItems', 'terms' => [], 'title' => null, 'welcome' => null, 'sidebar' => null, 'nav' => true])
{{-- The fixed full-screen stage every scene shares: backdrop, header, catalog and the AI Business Assistant chat dock. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $business->name.' · AI Business Assistant' }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--assistant-image: url('{{ $backdrop['avatarImage'] }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-stage data-scene="{{ $scene }}" data-page="{{ $scene }}" data-chat-dock="{{ $scene === 'info' ? 'center' : ($scene === 'staff' ? 'left' : 'right') }}">
        <div class="stage-loader" data-stage-loader role="status"><span class="business-monogram" aria-hidden="true">{{ mb_substr($business->name, 0, 1) }}</span><p>{{ $stageCopy['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="{{ $stageCopy['assistant'] }}" class="stage-character" data-anchor="{{ $backdrop['anchor'] }}">
        <p class="stage-tour" data-stage-tour aria-live="polite"></p>

        <header class="stage-header">
            <a href="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}" @if($scene !== 'home') data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" @endif class="flex min-w-0 items-center gap-3">
                <span class="business-monogram" aria-hidden="true">{{ mb_substr($business->name, 0, 1) }}</span>
                <span class="min-w-0"><span class="block truncate font-semibold tracking-tight">{{ $business->name }}</span><span class="block truncate text-xs opacity-75">{{ $business->city }} · {{ $business->country }}</span></span>
            </a>
            <div class="stage-tools">
                <x-sound-toggle :on="$stageCopy['sound_on']" :off="$stageCopy['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        {{ $welcome }}

@if(! $nav)
@elseif($sidebar)
        {{ $sidebar }}
@else
        <aside class="stage-dock" aria-label="{{ $stageCopy['explore'] }}">
            <p class="stage-eyebrow">{{ $stageCopy['explore'] }}</p>
            <nav class="stage-navigation">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}"
                        @if($item['exit']) data-stage-exit @if($item['tour']) data-tour-line="{{ $item['tour'] }}" @endif data-topic="{{ $item['topic'] }}" @endif
                        @if($item['key'] === $scene || ($scene === 'catalog-item' && $item['key'] === 'catalog') || ($scene === 'service-area' && $item['key'] === 'service')) aria-current="page" @endif>
                        <span class="nav-icon"><x-stage-icon :name="$item['icon']" /></span><span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <span class="stage-dock-footer">{{ $stageCopy['available'] }} · SME BUSINESS AI</span>
        </aside>
@endif

        {{ $slot }}

        <div data-chat-widget>
            @include('business.chat')
        </div>
        <noscript><p class="stage-noscript">Aktifkan JavaScript untuk menggunakan navigasi dan AI Business Assistant. {{ $business->phone }}</p></noscript>
    </main>
</body>
</html>
