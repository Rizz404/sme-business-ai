<?php

namespace App\Policies;

use App\Models\BusinessKnowledgeItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusinessKnowledgeItemPolicy extends BusinessScopedPolicy
{
    public function update(User $user, BusinessKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsBusiness($user, $knowledgeItem->business_id);
    }

    public function delete(User $user, BusinessKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsBusiness($user, $knowledgeItem->business_id);
    }
}
