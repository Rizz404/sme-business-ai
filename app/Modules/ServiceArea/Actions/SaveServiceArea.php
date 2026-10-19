<?php

namespace App\Modules\ServiceArea\Actions;

use App\Models\Business;
use App\Models\ServiceArea;
use Illuminate\Support\Str;

/**
 * Creates a service area or updates one, together with the photos added or
 * removed in the same form. A new area gets a slug from its name; an existing
 * one keeps its slug so guest links never break.
 */
class SaveServiceArea
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $imageIdsToDelete
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    public function handle(Business $business, array $attributes, array $imageIdsToDelete, array $newImages, ?ServiceArea $serviceArea = null): ServiceArea
    {
        if ($serviceArea) {
            $serviceArea->update($attributes);
        } else {
            $serviceArea = $business->serviceAreas()->create([...$attributes, 'slug' => $this->uniqueSlug($business, $attributes['name'])]);
        }

        $this->syncImages($serviceArea, $imageIdsToDelete, $newImages);

        return $serviceArea;
    }

    private function uniqueSlug(Business $business, string $name): string
    {
        $base = Str::slug($name) ?: 'area';
        $slug = $base;
        $suffix = 1;

        while ($business->serviceAreas()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  list<int>  $deleteIds
     * @param  list<array{url: string, alt?: ?string, tags?: ?string}>  $newImages
     */
    private function syncImages(ServiceArea $serviceArea, array $deleteIds, array $newImages): void
    {
        if ($deleteIds !== []) {
            $serviceArea->images()->whereIn('id', $deleteIds)->delete();
        }

        $sort = $serviceArea->images()->max('sort_order') + 1;

        foreach ($newImages as $row) {
            $serviceArea->images()->create([
                'image_url' => $row['url'],
                'alt_text' => $row['alt'] ?? $serviceArea->name,
                'tags' => collect(explode(',', $row['tags'] ?? ''))->map(fn ($t) => trim($t))->filter()->values()->all(),
                'sort_order' => $sort++,
            ]);
        }
    }
}
