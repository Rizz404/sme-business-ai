<x-layouts.admin title="Dashboard">
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            'catalog_items' => 'Catalog items',
            'service_areas' => 'Service areas',
            'knowledge_items' => 'Knowledge items',
            'pending_bookings' => 'Pending bookings',
            'open_handovers' => 'Open handovers',
        ] as $key => $label)
            <div @class([
                'rounded-card border p-4',
                'border-warning-border bg-warning-soft' => $key === 'open_handovers' && $stats[$key] > 0,
                'border-border bg-surface' => ! ($key === 'open_handovers' && $stats[$key] > 0),
            ])>
                <p class="text-2xl font-semibold text-text">{{ $stats[$key] }}</p>
                <p class="text-xs text-muted">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card flush>
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <p class="font-medium text-text">Open handovers</p>
                <a href="{{ route('admin.handovers.index') }}" class="text-xs text-muted hover:underline">View all</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($openHandovers as $handover)
                    <a href="{{ route('admin.handovers.show', $handover) }}" class="block px-4 py-3 hover:bg-surface-hover">
                        <p class="text-sm font-medium text-text">{{ ucwords(str_replace('_', ' ', $handover->reason)) }}</p>
                        <p class="mt-0.5 line-clamp-1 text-xs text-muted">{{ $handover->summary }}</p>
                    </a>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-subtle">No open handovers.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card flush>
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <p class="font-medium text-text">Recent bookings</p>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-muted hover:underline">View all</a>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentBookings as $booking)
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-text">{{ $booking->customer_name }} — {{ $booking->serviceArea->name }}</p>
                        <p class="mt-0.5 text-xs text-muted">{{ $booking->booking_date->toFormattedDateString() }} · {{ $booking->time_range }} · {{ $booking->attendees }} attendees · {{ ucfirst($booking->status) }}</p>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-subtle">No bookings yet.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
