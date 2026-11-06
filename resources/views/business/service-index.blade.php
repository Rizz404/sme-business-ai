<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['service_heading'].' · '.$business->name">
    <x-slot:caption>
        @if ($serviceNarrations['service'])
            @include('business.narrator', ['text' => $serviceNarrations['service'], 'key' => 'service', 'onStage' => true])
        @endif
    </x-slot:caption>

    <section class="stage-content @container" aria-label="{{ $labels['service_heading'] }}">
        <span class="stage-eyebrow">{{ $stageCopy['explore'] }}</span>
        <h1>{{ $labels['service_heading'] }}</h1>
        @if ($serviceAreas->isNotEmpty())
            @include('business.service-nav')
        @else
            <p class="stage-lead">{{ $stageCopy['service_empty'] }}</p>
        @endif
    </section>
</x-business-stage>
