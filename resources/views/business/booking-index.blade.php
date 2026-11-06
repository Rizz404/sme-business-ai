<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['booking_heading'].' · '.$business->name">
    <x-slot:caption>
        @include('business.narrator', ['text' => $wizard['intro'], 'key' => 'booking', 'onStage' => true])
    </x-slot:caption>

    @include('business.booking')
</x-business-stage>
