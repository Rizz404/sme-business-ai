<?php

namespace App\Models;

use App\Modules\Booking\Enums\BookingStatus;
use App\Modules\Booking\Enums\Purpose;
use App\Modules\Booking\Support\SlotAvailability;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference',
    'business_id',
    'service_area_id',
    'conversation_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'contact_type',
    'booking_date',
    'time_slot',
    'attendees',
    'purpose',
    'deposit_total',
    'status',
    'notes',
])]
class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = BookingStatus::Pending->value;

    public const STATUS_CONFIRMED = BookingStatus::Confirmed->value;

    public const STATUS_CANCELLED = BookingStatus::Cancelled->value;

    /**
     * Purposes a customer can mention so the team can prepare for the visit.
     */
    public const PURPOSES = [
        Purpose::Consultation->value,
        Purpose::Purchase->value,
        Purpose::Support->value,
        Purpose::Partnership->value,
        Purpose::Other->value,
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'deposit_total' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * "11:00–13:00" for a booking that starts at 11:00.
     */
    public function getTimeRangeAttribute(): string
    {
        return SlotAvailability::slotRange($this->time_slot);
    }

    /**
     * A short, unambiguous, non-sequential code guests can quote to staff.
     */
    public static function generateReference(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $reference = 'BK-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }
}
