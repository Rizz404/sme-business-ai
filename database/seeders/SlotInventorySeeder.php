<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\ServiceArea;
use App\Models\SlotInventory;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class SlotInventorySeeder extends Seeder
{
    /**
     * Every slot is two hours, so these are the slots the demo business sells.
     *
     * @var list<string>
     */
    private const SLOTS = ['09:00', '11:00', '13:00', '15:00'];

    /**
     * Parallel slots per service area, keyed by the area's slug.
     *
     * @var array<string, int>
     */
    private const TOTAL_UNITS = [
        'studio-front' => 4,
        'workshop-room' => 1,
        'online-consultation' => 6,
        'onsite-visit' => 2,
    ];

    public function run(): void
    {
        $business = Business::where('name', BusinessSeeder::NAME)->firstOrFail();

        foreach (self::TOTAL_UNITS as $areaSlug => $totalUnits) {
            $area = $business->serviceAreas()->where('slug', $areaSlug)->firstOrFail();

            $this->seedInventory($area, $totalUnits);
        }
    }

    /**
     * Opens every slot for the booking window, with a deterministic spread of
     * already-reserved slots so some slots read as busy or full.
     */
    private function seedInventory(ServiceArea $area, int $totalUnits): void
    {
        $today = CarbonImmutable::now($area->business->timezone)->startOfDay();
        $rows = [];

        for ($day = 0; $day <= SlotAvailability::MAX_ADVANCE_DAYS; $day++) {
            $date = $today->addDays($day)->toDateString();

            foreach (self::SLOTS as $slot) {
                $roll = crc32($area->slug.$date.$slot) % 100;

                $rows[] = [
                    'service_area_id' => $area->id,
                    'booking_date' => $date,
                    'time_slot' => $slot,
                    'total_units' => $totalUnits,
                    'reserved_units' => (int) floor((($roll / 100) ** 2) * ($totalUnits + 0.99)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            SlotInventory::upsert($chunk, ['service_area_id', 'booking_date', 'time_slot'], ['total_units']);
        }
    }
}
