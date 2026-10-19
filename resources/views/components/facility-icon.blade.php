@props(['name' => 'star'])
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('wifi')
            <path d="M2.5 8.5a14 14 0 0 1 19 0M5.5 12a9.5 9.5 0 0 1 13 0M8.5 15.5a5 5 0 0 1 7 0"/><circle cx="12" cy="19" r="1" fill="currentColor"/>
            @break
        @case('power')
            <path d="M9 3v5M15 3v5M6.5 8h11v3a5.5 5.5 0 0 1-11 0V8ZM12 16.5V21"/>
            @break
        @case('workspace')
            <rect x="4" y="5" width="16" height="10" rx="1.5"/><path d="M2 19h20M9 15v4M15 15v4"/>
            @break
        @case('parking')
            <rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9.5 16.5V7.5h3a2.5 2.5 0 0 1 0 5h-3"/>
            @break
        @case('camera')
            <path d="M4 8h3l1.5-2.5h7L17 8h3v11H4V8Z"/><circle cx="12" cy="13.5" r="3.5"/>
            @break
        @case('print')
            <path d="M7 9V4h10v5M7 17H4.5v-6.5h15V17H17M7 14h10v6H7v-6Z"/>
            @break
        @case('event')
            <rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4M9 15h6"/>
            @break
        @case('delivery')
            <circle cx="6" cy="17" r="2.5"/><circle cx="18" cy="17" r="2.5"/><path d="M8.5 17h7M6 14.5l2-6h5l2.5 6M13 8.5V6h3"/>
            @break
        @case('place')
            <path d="M12 21s-6.5-5.4-6.5-11a6.5 6.5 0 0 1 13 0c0 5.6-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/>
            @break
        @default
            <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.8l-5.2 2.8 1-5.8L3.5 9.7l5.9-.9L12 3.5Z"/>
    @endswitch
</svg>
