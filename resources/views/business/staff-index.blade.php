<x-business-stage :business="$business" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['staff_heading'].' · '.$business->name">
    <div class="stage-panels">
        <section class="stage-content staff-panel @container" aria-label="{{ $labels['staff_heading'] }}">
            <a href="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="panel-close" aria-label="{{ $stageCopy['back'] }}"><span aria-hidden="true">×</span></a>
            <p class="stage-eyebrow">{{ $business->name }}</p>
            <h2>{{ $labels['staff_heading'] }}</h2>
            @include('business.narrator', ['text' => $stageCopy['staff_intro'], 'key' => 'staff', 'inline' => true])

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" data-hero-quick-message="{{ $labels['nav_staff_q'] }}" class="stage-action">{{ $labels['staff_heading'] }} <span aria-hidden="true">↗</span></button>
                @if($staffLinks['whatsapp_url'])<a href="{{ $staffLinks['whatsapp_url'] }}" target="_blank" rel="noopener" class="wizard-secondary">{{ $wizard['whatsapp'] }}</a>@endif
                @if($staffLinks['phone_url'])<a href="{{ $staffLinks['phone_url'] }}" class="wizard-secondary">{{ $wizard['call_business'] }}</a>@endif
                @if($staffLinks['email_url'])<a href="{{ $staffLinks['email_url'] }}" class="wizard-secondary">{{ $wizard['email_business'] }}</a>@endif
            </div>

            <div class="staff-location">
                <h3>{{ $stageCopy['location'] }}</h3>
                <p>{{ $business->address }}, {{ $business->city }}, {{ $business->country }}</p>
                @if($business->phone)<p>{{ $business->phone }}</p>@endif
                @if($business->email)<p>{{ $business->email }}</p>@endif
                @if($business->instagram)<p>{{ $business->instagram }}</p>@endif
            </div>
        </section>
    </div>
</x-business-stage>
