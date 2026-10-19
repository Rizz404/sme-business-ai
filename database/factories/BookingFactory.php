<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Business;
use App\Models\ServiceArea;
use App\Modules\Booking\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Booking::generateReference(),
            'business_id' => Business::factory(),
            'service_area_id' => fn (array $attributes) => ServiceArea::factory()->create(['business_id' => $attributes['business_id']])->id,
            'customer_name' => fake()->name(),
            'customer_phone' => '+62 811 1111 222',
            'contact_type' => 'phone',
            'booking_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'attendees' => 2,
            'deposit_total' => 0,
            'status' => BookingStatus::Pending->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Confirmed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Cancelled->value]);
    }
}
