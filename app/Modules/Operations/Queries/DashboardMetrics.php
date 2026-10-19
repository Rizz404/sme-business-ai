<?php

namespace App\Modules\Operations\Queries;

use App\Models\Booking;
use App\Models\Business;
use App\Models\HandoverRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the business team sees first when they sign in: counts of what they manage
 * and the few things waiting for them.
 */
class DashboardMetrics
{
    /**
     * @return array{catalog_items: int, service_areas: int, knowledge_items: int, pending_bookings: int, open_handovers: int}
     */
    public function counts(Business $business): array
    {
        return [
            'catalog_items' => $business->catalogItems()->count(),
            'service_areas' => $business->serviceAreas()->count(),
            'knowledge_items' => $business->knowledgeItems()->count(),
            'pending_bookings' => $business->bookings()->where('status', Booking::STATUS_PENDING)->count(),
            'open_handovers' => $this->openHandovers($business)->count(),
        ];
    }

    /**
     * @return Collection<int, Booking>
     */
    public function recentBookings(Business $business, int $limit = 5): Collection
    {
        return $business->bookings()->latest()->take($limit)->with('serviceArea')->get();
    }

    /**
     * @return Collection<int, HandoverRequest>
     */
    public function latestOpenHandovers(Business $business, int $limit = 5): Collection
    {
        return $this->openHandovers($business)->latest()->take($limit)->with('conversation')->get();
    }

    /**
     * @return Builder<HandoverRequest>
     */
    private function openHandovers(Business $business): Builder
    {
        return HandoverRequest::whereHas('conversation', fn ($q) => $q->where('business_id', $business->id))
            ->where('status', HandoverRequest::STATUS_OPEN);
    }
}
