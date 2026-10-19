<?php

namespace App\Policies;

use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceAreaPolicy extends BusinessScopedPolicy
{
    public function update(User $user, ServiceArea $serviceArea): Response
    {
        return $this->ownsBusiness($user, $serviceArea->business_id);
    }

    public function delete(User $user, ServiceArea $serviceArea): Response
    {
        return $this->ownsBusiness($user, $serviceArea->business_id);
    }
}
