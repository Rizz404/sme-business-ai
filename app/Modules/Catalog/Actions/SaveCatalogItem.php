<?php

namespace App\Modules\Catalog\Actions;

use App\Models\Business;
use App\Models\CatalogItem;
use Illuminate\Support\Str;

/**
 * Creates a catalog item or updates one. A new item gets a slug from its name; an
 * existing one keeps its slug so guest links never break.
 */
class SaveCatalogItem
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Business $business, array $attributes, ?CatalogItem $catalogItem = null): CatalogItem
    {
        if ($catalogItem) {
            $catalogItem->update($attributes);

            return $catalogItem;
        }

        return $business->catalogItems()->create([...$attributes, 'slug' => $this->uniqueSlug($business, $attributes['name'])]);
    }

    private function uniqueSlug(Business $business, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 1;

        while ($business->catalogItems()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
