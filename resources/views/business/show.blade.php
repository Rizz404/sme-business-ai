<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:welcome>
        <div class="stage-welcome">
            <span class="stage-eyebrow">{{ $business->name }}</span>
            <h1>{{ $stageCopy['welcome'] }}<br><em>{{ $stageCopy['counter'] }}.</em></h1>
            <p>{{ $stageCopy['intro'] }}</p>
        </div>
    </x-slot:welcome>
</x-business-stage>
