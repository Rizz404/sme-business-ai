{{-- On the service scenes the side card becomes the area index, so the guest can step between areas without going back. --}}
<aside class="stage-dock" aria-label="{{ $labels['service_heading'] }}">
    <a href="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-dock-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['service_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($serviceAreas as $item)
            <a href="{{ route('business.service-area', ['businessSlug' => $business->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}"
                data-stage-exit
                @if (($serviceArea ?? null)?->slug === $item->slug) aria-current="page" @endif>
                @if ($item->images->first())
                    <img class="item-nav-thumb" src="{{ $item->images->first()->image_source }}" alt="" loading="lazy">
                @endif
                <span class="item-nav-text">
                    <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                    <span class="item-nav-price">{{ $terms['area_type'][$item->area_type] ?? Str::headline($item->area_type) }} · {{ $item->min_attendees > 1 ? $item->min_attendees.'–' : '' }}{{ $item->max_attendees }} {{ $stageCopy['attendees_unit'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
    <span class="stage-dock-footer">{{ $stageCopy['available'] }} · SME BUSINESS AI</span>
</aside>
