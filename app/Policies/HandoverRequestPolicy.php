<?php

namespace App\Policies;

use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoverRequestPolicy extends BusinessScopedPolicy
{
    public function view(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsBusiness($user, $handover->conversation->business_id);
    }

    public function update(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsBusiness($user, $handover->conversation->business_id);
    }
}
