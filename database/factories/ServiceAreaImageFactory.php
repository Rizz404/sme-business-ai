<?php

namespace Database\Factories;

use App\Models\ServiceArea;
use App\Models\ServiceAreaImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceAreaImage>
 */
class ServiceAreaImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_area_id' => ServiceArea::factory(),
            'image_url' => 'images/service/studio-front.svg',
            'alt_text' => fake()->sentence(3),
            'tags' => [],
            'sort_order' => 0,
        ];
    }
}
