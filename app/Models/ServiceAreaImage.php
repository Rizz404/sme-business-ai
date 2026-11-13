<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'service_area_id',
    'image_path',
    'image_url',
    'tags',
    'alt_text',
    'sort_order',
])]
class ServiceAreaImage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function getImageSourceAttribute(): ?string
    {
        if ($this->image_url) {
            $imageUrl = $this->image_url;
            $rasterPath = preg_replace('/\.svg$/', '.webp', $imageUrl);

            if (str_starts_with($imageUrl, 'images/') && is_file(public_path($rasterPath))) {
                $imageUrl = $rasterPath;
            }

            return str_starts_with($imageUrl, 'http') ? $imageUrl : asset($imageUrl);
        }

        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }
}
