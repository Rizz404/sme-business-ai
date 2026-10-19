<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$serviceArea->translatedName($locale).' · '.$business->name">
    @if($serviceAreas->isNotEmpty())
        <x-slot:sidebar>@include('business.service-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('business.service-scene')
    </div>
</x-business-stage>
