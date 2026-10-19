<?php

namespace App\Modules\Booking\Enums;

/**
 * Purposes a customer can mention so the team can prepare for the visit.
 */
enum Purpose: string
{
    case Consultation = 'consultation';
    case Purchase = 'purchase';
    case Support = 'support';
    case Partnership = 'partnership';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
