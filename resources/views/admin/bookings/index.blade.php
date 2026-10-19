<x-layouts.admin title="Bookings">
    <div class="mb-4 flex gap-2 text-sm">
        @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $value => $label)
            <a href="{{ route('admin.bookings.index', $value ? ['status' => $value] : []) }}"
                class="rounded-full px-3 py-1 {{ $status === $value || (! $status && $value === '') ? 'bg-primary text-on-primary' : 'border border-border-strong bg-surface text-muted' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-ui.table :columns="['Reference', 'Guest', 'Service', 'When', 'Fee', 'Status', '']" :empty="$bookings->isEmpty()" empty-text="No bookings found.">
        @foreach ($bookings as $booking)
            <tr>
                <td class="px-4 py-3 font-mono text-xs">{{ $booking->reference }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-text">{{ $booking->customer_name }}</p>
                    <p class="text-xs text-muted">{{ $booking->contact_type ? ucfirst($booking->contact_type).': ' : '' }}{{ $booking->customer_phone ?? $booking->customer_email }}</p>
                    @if ($booking->notes)<p class="mt-1 max-w-xs text-xs italic text-subtle">{{ $booking->notes }}</p>@endif
                </td>
                <td class="px-4 py-3">{{ $booking->serviceArea->name }}<p class="text-xs text-subtle">{{ $booking->attendees }} attendees{{ $booking->purpose ? ' · '.ucfirst($booking->purpose) : '' }}</p></td>
                <td class="px-4 py-3 text-xs text-muted">{{ $booking->booking_date->toFormattedDateString() }}<p>{{ $booking->time_range }}</p></td>
                <td class="px-4 py-3">{{ (float) $booking->deposit_total > 0 ? $business->currency.' '.number_format((float) $booking->deposit_total, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="match ($booking->status) { 'pending' => 'warning', 'confirmed' => 'success', default => 'neutral' }">{{ ucfirst($booking->status) }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    @if ($booking->status === 'pending')
                        <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="text-success hover:underline">Confirm</button>
                        </form>
                        <x-ui.confirm-form :action="route('admin.bookings.status', $booking)" method="PATCH" message="Cancel this booking?" confirm-label="Cancel booking" cancel-label="Keep" trigger-class="ml-3 text-danger hover:underline">
                            <x-slot:fields>
                                <input type="hidden" name="status" value="cancelled">
                            </x-slot:fields>
                            Cancel
                        </x-ui.confirm-form>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="mt-4">{{ $bookings->links() }}</div>
</x-layouts.admin>
