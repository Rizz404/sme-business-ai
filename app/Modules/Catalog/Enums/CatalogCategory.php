<?php

namespace App\Modules\Catalog\Enums;

/**
 * The kinds of offering a business catalog is grouped into.
 */
enum CatalogCategory: string
{
    case Product = 'product';
    case Service = 'service';
    case Package = 'package';
    case Consultation = 'consultation';
    case Digital = 'digital';
    case Addon = 'addon';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
