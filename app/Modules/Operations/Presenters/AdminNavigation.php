<?php

namespace App\Modules\Operations\Presenters;

/**
 * The admin catalog: where each entry leads and what it is called.
 */
final class AdminNavigation
{
    /**
     * @return list<array{route: string, label: string}>
     */
    public static function items(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['route' => 'admin.catalog-items.index', 'label' => 'Catalog'],
            ['route' => 'admin.service-areas.index', 'label' => 'Service areas'],
            ['route' => 'admin.knowledge-items.index', 'label' => 'Knowledge base'],
            ['route' => 'admin.bookings.index', 'label' => 'Bookings'],
            ['route' => 'admin.handovers.index', 'label' => 'Handovers'],
        ];
    }
}
