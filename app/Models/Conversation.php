<?php

namespace App\Models;

use App\Modules\Conversation\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'business_id',
    'guest_token',
    'customer_name',
    'customer_email',
    'locale',
    'status',
    'handover_summary',
    'current_scene',
    'selected_catalog_item_id',
    'selected_service_area_id',
    'selected_facility_id',
    'booking_state',
    'last_message_at',
])]
class Conversation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = ConversationStatus::Active->value;

    public const STATUS_HANDED_OVER = ConversationStatus::HandedOver->value;

    public const STATUS_CLOSED = ConversationStatus::Closed->value;

    /**
     * The UI scenes the guest can be in while talking to the AI Business Assistant.
     */
    public const SCENES = ['home', 'catalog', 'catalog_item', 'service', 'service_area', 'facilities', 'facility_detail', 'booking', 'handover'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'booking_state' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function selectedCatalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'selected_catalog_item_id');
    }

    public function selectedServiceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class, 'selected_service_area_id');
    }

    public function selectedFacility(): BelongsTo
    {
        return $this->belongsTo(BusinessKnowledgeItem::class, 'selected_facility_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function handoverRequest(): HasOne
    {
        return $this->hasOne(HandoverRequest::class)->latestOfMany();
    }

    public function isHandedOver(): bool
    {
        return $this->status === self::STATUS_HANDED_OVER;
    }
}
