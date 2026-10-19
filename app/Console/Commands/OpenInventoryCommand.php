<?php

namespace App\Console\Commands;

use App\Models\ServiceArea;
use App\Models\SlotInventory;
use App\Modules\Booking\Support\SlotAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Keeps the booking window rolling: every active service area gets its slots
 * opened up to the maximum advance days. Idempotent — existing rows keep their
 * reserved count and only the new days are inserted. An area with no slots yet
 * starts from the defaults in config/business.php.
 */
#[Signature('sme:open-inventory')]
#[Description('Open booking slots for every active service area up to the maximum advance days')]
class OpenInventoryCommand extends Command
{
    public function handle(): int
    {
        $created = 0;

        ServiceArea::query()->where('is_active', true)->with('business')->each(function (ServiceArea $area) use (&$created) {
            $slots = SlotInventory::query()
                ->where('service_area_id', $area->id)
                ->distinct()
                ->pluck('time_slot')
                ->all();

            $slots = $slots === [] ? config('business.default_slots') : $slots;
            $units = (int) SlotInventory::query()->where('service_area_id', $area->id)->latest('booking_date')->value('total_units')
                ?: (int) config('business.default_units');
            $today = CarbonImmutable::now($area->business->timezone)->startOfDay();
            $rows = [];

            for ($day = 0; $day <= SlotAvailability::MAX_ADVANCE_DAYS; $day++) {
                foreach ($slots as $slot) {
                    $rows[] = [
                        'service_area_id' => $area->id,
                        'booking_date' => $today->addDays($day)->toDateString(),
                        'time_slot' => $slot,
                        'total_units' => max(1, $units),
                        'reserved_units' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                $created += SlotInventory::insertOrIgnore($chunk);
            }
        });

        $this->components->info("Opened {$created} new slot(s).");

        return self::SUCCESS;
    }
}
