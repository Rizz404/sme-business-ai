<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['catalog_heading'].' · '.$business->name">
    <x-slot:caption>
        @if ($catalogNarrations['catalog'])
            @include('business.narrator', ['text' => $catalogNarrations['catalog'], 'key' => 'catalog', 'onStage' => true])
        @endif
    </x-slot:caption>

    <section class="stage-content @container" aria-label="{{ $labels['catalog_heading'] }}">
        <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
        <h1>{{ $labels['catalog_heading'] }}</h1>
        @if ($catalogItems->isNotEmpty())
            @include('business.catalog-nav')
        @else
            <p class="stage-lead">{{ $stageCopy['catalog_empty'] }}</p>
        @endif
    </section>
</x-business-stage>
