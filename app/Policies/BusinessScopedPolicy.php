<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Staff manage the records of their own business and nothing else. A record that
 * belongs to another business is answered with "not found", so its existence is
 * never revealed.
 */
abstract class BusinessScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentBusiness() !== null;
    }

    public function create(User $user): bool
    {
        return $user->currentBusiness() !== null;
    }

    protected function ownsBusiness(User $user, ?int $businessId): Response
    {
        $business = $user->currentBusiness();

        return $business !== null && $business->id === $businessId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
