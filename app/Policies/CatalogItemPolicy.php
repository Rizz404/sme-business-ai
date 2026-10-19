<?php

namespace App\Policies;

use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CatalogItemPolicy extends BusinessScopedPolicy
{
    public function update(User $user, CatalogItem $catalogItem): Response
    {
        return $this->ownsBusiness($user, $catalogItem->business_id);
    }

    public function delete(User $user, CatalogItem $catalogItem): Response
    {
        return $this->ownsBusiness($user, $catalogItem->business_id);
    }
}
