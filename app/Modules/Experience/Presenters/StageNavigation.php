<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Business;

/**
 * The main catalog of the stage. Sections that live on another page are marked
 * so the stage can fade out, as if the assistant were walking the guest over.
 */
final class StageNavigation
{
    /**
     * @param  array<string, string>  $labels
     * @return list<array{key: string, icon: string, label: string, chip: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    public static function for(Business $business, string $locale, array $labels): array
    {
        $tour = StageCopy::narration($locale);
        $url = fn (string $route) => route($route, ['businessSlug' => $business->slug, 'lang' => $locale]);

        return [
            ['key' => 'catalog', 'icon' => 'bag', 'label' => $labels['catalog_heading'], 'chip' => $labels['chip_catalog'], 'href' => $url('business.catalog'), 'exit' => true, 'tour' => $tour['tour_catalog'], 'topic' => $labels['nav_catalog_q']],
            ['key' => 'service', 'icon' => 'pin', 'label' => $labels['service_heading'], 'chip' => $labels['chip_service'], 'href' => $url('business.service'), 'exit' => true, 'tour' => $tour['tour_service'], 'topic' => $labels['nav_service_q']],
            ['key' => 'facilities', 'icon' => 'sparkle', 'label' => $labels['facilities_heading'], 'chip' => $labels['chip_facilities'], 'href' => $url('business.facilities'), 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['nav_facilities_q']],
            ['key' => 'info', 'icon' => 'info', 'label' => $labels['info_heading'], 'chip' => $labels['chip_info'], 'href' => $url('business.info'), 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['nav_info_q']],
            ['key' => 'booking', 'icon' => 'calendar', 'label' => $labels['booking_heading'], 'chip' => $labels['chip_booking'], 'href' => $url('business.booking'), 'exit' => true, 'tour' => $tour['tour_booking'], 'topic' => $labels['nav_booking_q']],
            ['key' => 'staff', 'icon' => 'chat', 'label' => $labels['staff_heading'], 'chip' => $labels['chip_staff'], 'href' => $url('business.staff'), 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['nav_staff_q']],
        ];
    }
}
