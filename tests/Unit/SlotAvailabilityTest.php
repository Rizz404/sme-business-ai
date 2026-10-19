<?php

namespace Tests\Unit;

use App\Models\ServiceArea;
use App\Modules\Booking\Support\SlotAvailability;
use Tests\TestCase;

class SlotAvailabilityTest extends TestCase
{
    public function test_a_slot_is_a_two_hour_range(): void
    {
        $this->assertSame('11:00–13:00', SlotAvailability::slotRange('11:00'));
        $this->assertSame('21:00–23:00', SlotAvailability::slotRange('21:00'));
    }

    public function test_a_party_must_fit_the_area_capacity(): void
    {
        $area = new ServiceArea(['min_attendees' => 4, 'max_attendees' => 8]);
        $availability = new SlotAvailability;

        $this->assertFalse($availability->fitsAttendees($area, 3));
        $this->assertTrue($availability->fitsAttendees($area, 4));
        $this->assertTrue($availability->fitsAttendees($area, 8));
        $this->assertFalse($availability->fitsAttendees($area, 9));
    }
}
