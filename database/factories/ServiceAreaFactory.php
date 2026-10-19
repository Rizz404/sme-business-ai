<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\ServiceArea;
use App\Modules\ServiceArea\Enums\AreaType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceArea>
 */
class ServiceAreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'business_id' => Business::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'area_type' => AreaType::Storefront->value,
            'min_attendees' => 1,
            'max_attendees' => 4,
            'features' => [],
            'booking_fee' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forAttendees(int $min, int $max): static
    {
        return $this->state(fn () => ['min_attendees' => $min, 'max_attendees' => $max]);
    }

    public function withFee(float $fee): static
    {
        return $this->state(fn () => ['booking_fee' => $fee]);
    }
}
