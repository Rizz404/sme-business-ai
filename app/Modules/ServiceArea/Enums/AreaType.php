<?php

namespace App\Modules\ServiceArea\Enums;

/**
 * Where a service area takes place.
 */
enum AreaType: string
{
    case Storefront = 'storefront';
    case Workshop = 'workshop';
    case Online = 'online';
    case OnSite = 'onsite';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
