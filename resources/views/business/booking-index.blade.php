<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['booking_heading'].' · '.$business->name">
    <div class="stage-panels">
        @include('business.booking')
    </div>
</x-business-stage>
