{{-- The card index of the service sheet. --}}
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
