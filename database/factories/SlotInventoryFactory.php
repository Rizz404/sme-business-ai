<?php

namespace Database\Factories;

use App\Models\ServiceArea;
use App\Models\SlotInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlotInventory>
 */
class SlotInventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_area_id' => ServiceArea::factory(),
            'booking_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'total_units' => 2,
            'reserved_units' => 0,
        ];
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => ['reserved_units' => $attributes['total_units']]);
    }
}
