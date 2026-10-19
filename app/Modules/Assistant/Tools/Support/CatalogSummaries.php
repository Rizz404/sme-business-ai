<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\Business;
use App\Models\CatalogItem;
use App\Models\ServiceArea;

/**
 * The compact facts about a catalog item or service area that both the model and
 * the customer's cards are given.
 */
final class CatalogSummaries
{
    /**
     * @return array<string, mixed>
     */
    public static function catalogItem(CatalogItem $item, Business $business, string $locale): array
    {
        return [
            'catalog_item_slug' => $item->slug,
            'name' => $item->translatedName($locale),
            'category' => $item->category,
            'price' => (float) $item->price,
            'currency' => $business->currency,
            'delivery' => $item->delivery,
            'tags' => $item->tags ?? [],
            'requirements' => $item->requirements ?? [],
            'is_unavailable' => $item->is_unavailable,
            'image_url' => $item->image_source,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function serviceArea(ServiceArea $area, Business $business, string $locale): array
    {
        return [
            'name' => $area->translatedName($locale),
            'area_type' => $area->area_type,
            'min_attendees' => $area->min_attendees,
            'max_attendees' => $area->max_attendees,
            'features' => $area->features ?? [],
            'booking_fee' => (float) $area->booking_fee,
            'minimum_order' => $area->minimum_order !== null ? (float) $area->minimum_order : null,
            'currency' => $business->currency,
            'thumbnail_url' => $area->images->first()?->image_source,
        ];
    }

    public static function catalogItemBySlug(Business $business, string $slug): ?CatalogItem
    {
        return $business->catalogItems()->where('is_active', true)->where('slug', $slug)->first();
    }

    public static function serviceAreaBySlug(Business $business, string $slug): ?ServiceArea
    {
        return $business->serviceAreas()->where('is_active', true)->where('slug', $slug)->first();
    }
}
