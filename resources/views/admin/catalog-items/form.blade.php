@php($isEdit = $catalogItem->exists)

<x-layouts.admin :title="$isEdit ? 'Edit catalog item' : 'Add catalog item'">
    <form method="POST" action="{{ $isEdit ? route('admin.catalog-items.update', $catalogItem) : route('admin.catalog-items.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.select name="category" label="Category">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $catalogItem->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="price" type="number" label="Price" step="500" :value="old('price', $catalogItem->price)" required />
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $catalogItem->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $catalogItem->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $catalogItem->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $catalogItem->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $catalogItem->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $catalogItem->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Details">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.input name="delivery" label="Delivery" :value="old('delivery', $catalogItem->delivery)" placeholder="onsite, pickup, delivery, online, shipping" />
                <x-ui.input name="duration_minutes" type="number" label="Duration (minutes)" min="0" :value="old('duration_minutes', $catalogItem->duration_minutes)" />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $catalogItem->sort_order)" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="tags" label="Tags (comma-separated)" :value="old('tags', implode(', ', $catalogItem->tags ?? []))" placeholder="popular, new, promo, local, eco, bestseller" />
                <x-ui.input name="requirements" label="Requirements (comma-separated)" :value="old('requirements', implode(', ', $catalogItem->requirements ?? []))" placeholder="appointment, deposit, documents, site_visit" />
                <x-ui.input name="image_url" label="Image path or URL" :value="old('image_url', $catalogItem->image_url)" placeholder="images/catalog/logo-design.svg or https://…" class="sm:col-span-2" />
            </div>
            <div class="mt-4 flex flex-wrap gap-6">
                <x-ui.checkbox name="is_featured" label="Featured" :checked="old('is_featured', $catalogItem->is_featured)" />
                <x-ui.checkbox name="is_unavailable" label="Sold out today" :checked="old('is_unavailable', $catalogItem->is_unavailable)" />
                <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $catalogItem->is_active)" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.catalog-items.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save catalog item</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
