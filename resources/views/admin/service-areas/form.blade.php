@php($isEdit = $serviceArea->exists)

<x-layouts.admin :title="$isEdit ? 'Edit service area' : 'Add service area'">
    <form method="POST" action="{{ $isEdit ? route('admin.service-areas.update', $serviceArea) : route('admin.service-areas.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $serviceArea->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $serviceArea->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $serviceArea->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $serviceArea->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $serviceArea->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $serviceArea->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Capacity & fees">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.select name="area_type" label="Type">
                    @foreach (\App\Models\ServiceArea::areaTypes() as $type)
                        <option value="{{ $type }}" @selected(old('area_type', $serviceArea->area_type) === $type)>{{ \Illuminate\Support\Str::headline($type) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="min_attendees" type="number" label="Min attendees" min="1" :value="old('min_attendees', $serviceArea->min_attendees)" required />
                <x-ui.input name="max_attendees" type="number" label="Max attendees" min="1" :value="old('max_attendees', $serviceArea->max_attendees)" required />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $serviceArea->sort_order)" />
                <x-ui.input name="booking_fee" type="number" label="Booking fee / slot" step="1000" min="0" :value="old('booking_fee', $serviceArea->booking_fee ?? 0)" required />
                <x-ui.input name="minimum_order" type="number" label="Minimum spend" step="1000" min="0" :value="old('minimum_order', $serviceArea->minimum_order)" />
            </div>
            <x-ui.input name="features" label="Features (comma-separated)" :value="old('features', implode(', ', $serviceArea->features ?? []))" placeholder="wifi, power_outlet, air_conditioning, pet_friendly" class="mt-4" />
            <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $serviceArea->is_active)" class="mt-4" />
        </x-ui.card>

        <x-ui.card heading="Photos">
            @if ($isEdit && $serviceArea->images->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($serviceArea->images as $image)
                        <div class="flex items-center gap-3 rounded-control border border-border p-2">
                            <img src="{{ $image->image_source }}" alt="{{ $image->alt_text }}" class="h-14 w-20 rounded-sm object-cover">
                            <div class="flex-1 text-xs text-muted">
                                <p class="truncate">{{ $image->image_url }}</p>
                                <p>Tags: {{ implode(', ', $image->tags ?? []) }}</p>
                            </div>
                            <label class="flex items-center gap-1 text-xs text-danger">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="rounded-sm border-border-strong">
                                Delete
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="mt-4 text-xs text-muted">Add photos by path under public/ or by URL. Tag them so the AI Business Assistant can pick the right one — e.g. "sofa, indoor" or "garden, terrace".</p>
            @for ($i = 0; $i < 3; $i++)
                <div class="mt-2 grid grid-cols-6 gap-2">
                    <input type="text" name="new_images[{{ $i }}][url]" aria-label="Photo {{ $i + 1 }} path or URL" placeholder="images/service/… or https://…" class="col-span-3 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][tags]" aria-label="Photo {{ $i + 1 }} tags" placeholder="showcase, storefront" class="col-span-2 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][alt]" aria-label="Photo {{ $i + 1 }} alt text" placeholder="Alt text" class="rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                </div>
            @endfor
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.service-areas.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save service area</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
