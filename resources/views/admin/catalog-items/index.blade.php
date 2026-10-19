<x-layouts.admin title="Catalog">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.catalog-items.create')" size="sm">Add catalog item</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Category', 'Price', 'Status', '']" :empty="$catalogItems->isEmpty()" empty-text="No catalog items yet.">
        @foreach ($catalogItems as $catalogItem)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $catalogItem->name }}@if ($catalogItem->is_featured)<x-ui.badge tone="warning" class="ml-2">Featured</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($catalogItem->category) }}</td>
                <td class="px-4 py-3">{{ $business->currency }} {{ number_format((float) $catalogItem->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$catalogItem->is_active ? 'success' : 'neutral'">{{ $catalogItem->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                    @if ($catalogItem->is_unavailable)
                        <x-ui.badge tone="danger" class="ml-1">Sold out</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.catalog-items.edit', $catalogItem) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.catalog-items.destroy', $catalogItem)" message="Delete this catalog item?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
