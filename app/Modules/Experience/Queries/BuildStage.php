<?php

namespace App\Modules\Experience\Queries;

use App\Models\Business;
use App\Models\BusinessKnowledgeItem;
use App\Modules\Booking\Support\BookingHandover;
use App\Modules\Booking\Support\SlotAvailability;
use App\Modules\Experience\Presenters\SceneNarrations;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Presenters\StageNavigation;

/**
 * Everything the shared stage shell needs, for whichever scene the guest has
 * walked into.
 */
class BuildStage
{
    public function __construct(
        private readonly BookingHandover $handover,
        private readonly SlotAvailability $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Business $business, string $locale, string $scene): array
    {
        $catalogItems = $business->catalogItems()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $serviceAreas = $business->serviceAreas()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $business->knowledgeItems()->where('is_active', true)
            ->whereIn('category', [BusinessKnowledgeItem::CATEGORY_FACILITIES, BusinessKnowledgeItem::CATEGORY_EVENTS, BusinessKnowledgeItem::CATEGORY_LOCATION])
            ->orderBy('sort_order')->get()->values();

        $labels = StageCopy::labels($locale);

        return [
            'business' => $business,
            'catalogItems' => $catalogItems,
            'serviceAreas' => $serviceAreas,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => StageBackdrop::for($scene),
            'navItems' => StageNavigation::for($business, $locale, $labels),
            'supportedLocales' => config('business.locales'),
            'labels' => $labels,
            'stageCopy' => StageCopy::copy($locale),
            'terms' => StageCopy::terms($locale),
            'wizard' => StageCopy::wizard($locale),
            'narration' => StageCopy::narration($locale),
            'catalogNarrations' => SceneNarrations::catalog($business, $catalogItems, $locale),
            'serviceNarrations' => SceneNarrations::service($business, $serviceAreas, $locale),
            'staffLinks' => $this->handover->forStaff($business, $locale),
            'today' => now($business->timezone)->toDateString(),
            'maxAdvanceDays' => SlotAvailability::MAX_ADVANCE_DAYS,
            'slots' => collect($this->availability->slotsFor($business))
                ->map(fn (string $slot) => ['value' => $slot, 'label' => SlotAvailability::slotRange($slot)])
                ->all(),
            'infoItems' => $business->knowledgeItems()->where('is_active', true)
                ->whereIn('category', [BusinessKnowledgeItem::CATEGORY_GENERAL, BusinessKnowledgeItem::CATEGORY_POLICIES, BusinessKnowledgeItem::CATEGORY_FAQ])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => SceneNarrations::scenes($business, $facilities, $locale),
        ];
    }
}
