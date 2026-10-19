<x-layouts.admin title="Service areas">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.service-areas.create')" size="sm">Add service area</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Type', 'Capacity', 'Fee', 'Images', 'Status', '']" :empty="$serviceAreas->isEmpty()" empty-text="No service areas yet.">
        @foreach ($serviceAreas as $serviceArea)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $serviceArea->name }}</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($serviceArea->area_type) }}</td>
                <td class="px-4 py-3">{{ $serviceArea->min_attendees }}–{{ $serviceArea->max_attendees }} attendees</td>
                <td class="px-4 py-3">{{ (float) $serviceArea->booking_fee > 0 ? $business->currency.' '.number_format((float) $serviceArea->booking_fee, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">{{ $serviceArea->images_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$serviceArea->is_active ? 'success' : 'neutral'">{{ $serviceArea->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.service-areas.edit', $serviceArea) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.service-areas.destroy', $serviceArea)" message="Delete this service area?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
