<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$catalogItem->translatedName($locale).' · '.$business->name">
    <x-slot:caption>
        @include('business.narrator', ['text' => $catalogNarrations['item'][$catalogItem->slug], 'key' => 'item-'.$catalogItem->slug, 'onStage' => true])
    </x-slot:caption>

    @include('business.catalog-scene')
</x-business-stage>
