{{-- The card index of the catalog sheet, grouped by category. --}}
<nav class="item-nav">
        @foreach ($catalogItems->groupBy('category') as $category => $group)
            <p class="item-nav-heading">{{ $terms['category'][$category] ?? Str::headline($category) }}</p>
            @foreach ($group as $item)
                <a href="{{ route('business.catalog-item', ['businessSlug' => $business->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}"
                    data-stage-exit
                    @if (($catalogItem ?? null)?->slug === $item->slug) aria-current="page" @endif>
                    @if ($item->image_source)
                        <img class="item-nav-thumb" src="{{ $item->image_source }}" alt="" loading="lazy">
                    @endif
                    <span class="item-nav-text">
                        <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                        <span class="item-nav-price">{{ $business->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}@if ($item->is_unavailable)<span class="item-nav-badge">{{ $stageCopy['unavailable'] }}</span>@endif</span>
                    </span>
                </a>
            @endforeach
        @endforeach
    </nav>
