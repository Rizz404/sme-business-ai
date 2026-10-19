<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SME Business AI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--assistant-image: url('{{ $avatarImage }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage">
        <div class="stage-loader" data-stage-loader role="status"><span class="business-monogram" aria-hidden="true">S</span><p>{{ $opening['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="" class="stage-character" data-anchor="left">

        <header class="stage-header">
            <span class="flex min-w-0 items-center gap-3">
                <span class="business-monogram" aria-hidden="true">S</span>
                <span class="block truncate font-semibold tracking-tight">SME Business AI</span>
            </span>
            <div class="stage-tools">
                <x-sound-toggle :on="$opening['sound_on']" :off="$opening['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <section class="opening-panel" aria-labelledby="opening-title">
            <p class="stage-eyebrow">AI ASSISTANT</p>
            <h1 id="opening-title">{{ $opening['welcome'] }}<br><em>SME Business AI</em></h1>
            <p class="opening-tagline">{{ $opening['tagline'] }}</p>

            @if ($businesses->count() === 1)
                @php
                    $businessSlug = $businesses->first()->slug;
                    $openingLinks = [
                        'catalog' => route('business.catalog', ['businessSlug' => $businessSlug, 'lang' => $locale]),
                        'service' => route('business.service', ['businessSlug' => $businessSlug, 'lang' => $locale]),
                        'booking' => route('business.booking', ['businessSlug' => $businessSlug, 'lang' => $locale]),
                        'staff' => route('business.staff', ['businessSlug' => $businessSlug, 'lang' => $locale]),
                    ];
                @endphp
                <ul class="opening-links">
                    @foreach ($openingLinks as $key => $href)
                        <li><a data-stage-exit href="{{ $href }}"><span>{{ $opening[$key] }}</span><span aria-hidden="true">›</span></a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="opening-enter">
            @forelse ($businesses as $business)
                <a href="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}" class="opening-button" data-stage-exit>
                    {{ str_replace(':name', $business->name, $opening['enter']) }} <span aria-hidden="true">→</span>
                </a>
            @empty
                <p class="opening-empty">{{ $opening['empty'] }}</p>
            @endforelse
        </div>
    </main>
</body>
</html>
