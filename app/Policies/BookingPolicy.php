<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy extends BusinessScopedPolicy
{
    public function update(User $user, Booking $booking): Response
    {
        return $this->ownsBusiness($user, $booking->business_id);
    }
}
