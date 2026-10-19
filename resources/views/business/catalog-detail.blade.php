<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$catalogItem->translatedName($locale).' · '.$business->name">
    @if($catalogItems->isNotEmpty())
        <x-slot:sidebar>@include('business.catalog-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('business.catalog-scene')
    </div>
</x-business-stage>
