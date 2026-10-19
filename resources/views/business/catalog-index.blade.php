<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['catalog_heading'].' · '.$business->name">
    <x-slot:welcome>
        <div class="stage-welcome stage-welcome-scene" data-anchor="{{ $backdrop['text'] }}">
            <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
            <h1>{{ $labels['catalog_heading'] }}</h1>
            @if ($catalogNarrations['catalog'])
                @include('business.narrator', ['text' => $catalogNarrations['catalog'], 'key' => 'catalog', 'onStage' => true])
            @else
                <p>{{ $stageCopy['catalog_empty'] }}</p>
            @endif
        </div>
    </x-slot:welcome>

    @if ($catalogItems->isNotEmpty())
        <x-slot:sidebar>@include('business.catalog-nav')</x-slot:sidebar>
    @endif
</x-business-stage>
