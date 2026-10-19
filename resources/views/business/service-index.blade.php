<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['service_heading'].' · '.$business->name">
    <x-slot:welcome>
        <div class="stage-welcome stage-welcome-scene" data-anchor="{{ $backdrop['text'] }}">
            <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
            <h1>{{ $labels['service_heading'] }}</h1>
            @if ($serviceNarrations['service'])
                @include('business.narrator', ['text' => $serviceNarrations['service'], 'key' => 'service', 'onStage' => true])
            @else
                <p>{{ $stageCopy['service_empty'] }}</p>
            @endif
        </div>
    </x-slot:welcome>

    @if ($serviceAreas->isNotEmpty())
        <x-slot:sidebar>@include('business.service-nav')</x-slot:sidebar>
    @endif
</x-business-stage>
