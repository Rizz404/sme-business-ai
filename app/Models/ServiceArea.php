<?php

namespace App\Models;

use App\Modules\ServiceArea\Enums\AreaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'business_id',
    'name',
    'slug',
    'description',
    'translations',
    'area_type',
    'min_attendees',
    'max_attendees',
    'features',
    'booking_fee',
    'minimum_order',
    'is_active',
    'sort_order',
])]
class ServiceArea extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_STOREFRONT = AreaType::Storefront->value;

    public const TYPE_WORKSHOP = AreaType::Workshop->value;

    public const TYPE_ONLINE = AreaType::Online->value;

    public const TYPE_ONSITE = AreaType::OnSite->value;

    /**
     * @return list<string>
     */
    public static function areaTypes(): array
    {
        return AreaType::values();
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'features' => 'array',
            'booking_fee' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceAreaImage::class)->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(SlotInventory::class);
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
