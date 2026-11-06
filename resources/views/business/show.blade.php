<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:caption>
        @include('business.narrator', ['text' => $stageCopy['intro'], 'key' => 'home', 'onStage' => true])
    </x-slot:caption>

    <section class="stage-content stage-home @container" aria-label="{{ $business->name }}">
        <span class="stage-eyebrow">{{ $business->name }}</span>
        <h1>{{ $stageCopy['welcome'] }} <em>{{ $stageCopy['counter'] }}.</em></h1>
        @if ($about = $business->translatedDescription($locale))
            <p class="stage-lead">{{ $about }}</p>
        @endif

        <ul class="destination-list" aria-label="{{ $stageCopy['explore'] }}">
            @foreach ($navItems as $item)
                <li>
                    <a href="{{ $item['href'] }}" data-stage-exit @if($item['tour']) data-tour-line="{{ $item['tour'] }}" @endif data-topic="{{ $item['topic'] }}">
                        <span class="destination-icon"><x-stage-icon :name="$item['icon']" /></span>
                        <span class="destination-text">
                            <span class="destination-name">{{ $item['label'] }}</span>
                            <span class="destination-hint">{{ $item['chip'] }}</span>
                        </span>
                        <span class="destination-go" aria-hidden="true">›</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="home-meta">
            @if ($business->opening_time && $business->closing_time)
                <span class="info-fact">{{ $stageCopy['hours'] }} · {{ substr($business->opening_time, 0, 5) }}–{{ substr($business->closing_time, 0, 5) }}</span>
            @endif
            <span class="info-fact">{{ $business->city }}, {{ $business->country }}</span>
        </div>
    </section>
</x-business-stage>
