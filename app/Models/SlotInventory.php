<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The only source of truth for slot availability. Stands in for a real
 * scheduling or booking-system adapter for V1: the AI must reach this
 * inventory (or its future adapter) through a tool call, never answer from
 * its own memory.
 */
#[Fillable([
    'service_area_id',
    'booking_date',
    'time_slot',
    'total_units',
    'reserved_units',
])]
class SlotInventory extends Model
{
    use HasFactory;

    protected $table = 'slot_inventory';

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function openUnits(): int
    {
        return max(0, $this->total_units - $this->reserved_units);
    }

    public function isAvailable(): bool
    {
        return $this->openUnits() > 0;
    }
}
