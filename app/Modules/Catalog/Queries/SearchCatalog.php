<?php

namespace App\Modules\Catalog\Queries;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The active catalog, narrowed by keyword, category, tag or budget.
 */
class SearchCatalog
{
    /**
     * @return Collection<int, CatalogItem>
     */
    public function handle(Business $business, string $query = '', ?string $category = null, ?string $tag = null, ?float $maxPrice = null, int $limit = 6): Collection
    {
        $query = SearchText::normalize($query);

        return $business->catalogItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (CatalogItem $item) => ! $tag || $item->hasTag($tag))
            ->filter(fn (CatalogItem $item) => $maxPrice === null || (float) $item->price <= $maxPrice)
            ->filter(fn (CatalogItem $item) => SearchText::matches(implode(' ', [
                $item->name,
                $item->description,
                $item->category,
                implode(' ', $item->tags ?? []),
                collect($item->translations ?? [])->flatten()->implode(' '),
            ]), $query))
            ->take($limit);
    }
}
