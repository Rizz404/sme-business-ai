<?php

namespace App\Models;

use App\Modules\Catalog\Enums\CatalogCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One product or service in the business catalog. The price here is the only price the
 * AI Business Assistant may ever quote, and only after reading it through a tool call.
 */
#[Fillable([
    'business_id',
    'category',
    'name',
    'slug',
    'description',
    'translations',
    'price',
    'image_url',
    'tags',
    'requirements',
    'delivery',
    'duration_minutes',
    'is_featured',
    'is_unavailable',
    'is_active',
    'sort_order',
])]
class CatalogItem extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORY_PRODUCT = CatalogCategory::Product->value;

    public const CATEGORY_SERVICE = CatalogCategory::Service->value;

    public const CATEGORY_PACKAGE = CatalogCategory::Package->value;

    public const CATEGORY_CONSULTATION = CatalogCategory::Consultation->value;

    public const CATEGORY_DIGITAL = CatalogCategory::Digital->value;

    public const CATEGORY_ADDON = CatalogCategory::Addon->value;

    /**
     * Tags a customer can filter on when narrowing the catalog.
     */
    public const FILTER_TAGS = ['popular', 'new', 'promo', 'local', 'eco', 'bestseller'];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return CatalogCategory::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'requirements' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_unavailable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The picture to show: a full URL as stored, or a file under public/.
     */
    public function getImageSourceAttribute(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http') ? $this->image_url : asset($this->image_url);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }

    public function translatedName(string $locale): string
    {
        $value = $this->translations[$locale]['name'] ?? null;

        return filled($value) ? $value : $this->name;
    }

    public function translatedDescription(string $locale): ?string
    {
        $value = $this->translations[$locale]['description'] ?? null;

        return filled($value) ? $value : $this->description;
    }
}
