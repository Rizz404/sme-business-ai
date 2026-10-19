<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Business;
use App\Models\Conversation;

/**
 * Remembers where in the UI the guest is, so the assistant can answer for "this
 * drink" or the booking they are filling in. Only values that exist for
 * this business are kept; nothing personal is stored here.
 */
class RememberUiContext
{
    /**
     * @param  array{scene?: ?string, selected_catalog_item?: ?string, selected_service_area?: ?string, selected_facility?: ?int, booking?: ?array<string, mixed>}  $context
     */
    public function handle(Business $business, Conversation $conversation, array $context): void
    {
        $updates = [];

        if (in_array($context['scene'] ?? null, Conversation::SCENES, true)) {
            $updates['current_scene'] = $context['scene'];
        }

        if (filled($context['selected_catalog_item'] ?? null)) {
            $catalogItem = $business->catalogItems()->where('is_active', true)->where('slug', $context['selected_catalog_item'])->first();

            if ($catalogItem) {
                $updates['selected_catalog_item_id'] = $catalogItem->id;
            }
        }

        if (filled($context['selected_service_area'] ?? null)) {
            $area = $business->serviceAreas()->where('is_active', true)->where('slug', $context['selected_service_area'])->first();

            if ($area) {
                $updates['selected_service_area_id'] = $area->id;
            }
        }

        if (filled($context['selected_facility'] ?? null)) {
            $facility = $business->knowledgeItems()->where('is_active', true)->whereKey($context['selected_facility'])->first();

            if ($facility) {
                $updates['selected_facility_id'] = $facility->id;
            }
        }

        if (array_key_exists('booking', $context)) {
            $updates['booking_state'] = $context['booking'] ?: null;
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }
}
