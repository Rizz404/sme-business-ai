<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$serviceArea->translatedName($locale).' · '.$business->name">
    <x-slot:caption>
        @include('business.narrator', ['text' => $serviceNarrations['area'][$serviceArea->slug], 'key' => 'area-'.$serviceArea->slug, 'onStage' => true])
    </x-slot:caption>

    @include('business.service-scene')
</x-business-stage>
