<?php

namespace App\Modules\Conversation\Enums;

/**
 * Why the assistant asked the team to take over.
 */
enum HandoverReason: string
{
    case SpecialRequest = 'special_request';
    case Complaint = 'complaint';
    case GroupBooking = 'group_booking';
    case PrivateEvent = 'private_event';
    case CustomOrder = 'custom_order';
    case PaymentIssue = 'payment_issue';
    case LowConfidence = 'low_confidence';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
