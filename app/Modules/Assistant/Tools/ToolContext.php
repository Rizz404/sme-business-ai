<?php

namespace App\Modules\Assistant\Tools;

use App\Models\Business;
use App\Models\Conversation;

/**
 * What a tool may act on: one business, one conversation, in the customer's language.
 */
final readonly class ToolContext
{
    public function __construct(
        public Business $business,
        public Conversation $conversation,
        public string $locale,
    ) {}
}
