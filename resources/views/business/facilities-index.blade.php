<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['facilities_heading'].' · '.$business->name">
    <x-slot:caption>
        @if ($sceneNarrations['facilities'])
            @include('business.narrator', ['text' => $sceneNarrations['facilities'], 'key' => 'facilities', 'onStage' => true])
        @endif
    </x-slot:caption>

    <section class="stage-content @container" aria-label="{{ $labels['facilities_heading'] }}">
        <a href="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="panel-close" aria-label="{{ $stageCopy['back'] }}"><span aria-hidden="true">×</span></a>
        <p class="stage-eyebrow">{{ $labels['chat_heading'] }}</p>
        <h2>{{ $labels['facilities_heading'] }}</h2>

        <div class="facility-grid">
            @forelse ($facilities as $item)
                @php $title = $item->translatedTitle($locale); @endphp
                <a href="{{ route('business.facility', ['businessSlug' => $business->slug, 'facilityId' => $item->id, 'lang' => $locale]) }}" data-stage-exit class="facility-card">
                    <span class="facility-card-photo">
                        @if ($item->image_source)
                            <img src="{{ $item->image_source }}" alt="" loading="lazy">
                        @endif
                        <span class="facility-card-icon"><x-facility-icon :name="$item->iconName()" /></span>
                    </span>
                    <span class="facility-card-body">
                        <span class="facility-card-title">{{ $title }}</span>
                        <span class="facility-card-text">{{ $item->translatedBody($locale) }}</span>
                    </span>
                    <span class="facility-card-go" aria-hidden="true">›</span>
                </a>
            @empty
                <p class="text-stone-500">{{ $stageCopy['empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-business-stage>
